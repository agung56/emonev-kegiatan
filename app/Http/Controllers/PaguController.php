<?php

namespace App\Http\Controllers;

use App\Models\Pagu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaguController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $pagus = Pagu::with(['details.komponen', 'komponens.details'])->latest()->get();

        // Hitung total terpakai per pagu_detail dari kegiatan_anggarans
        // Filter per tahun anggaran agar tidak cross-tahun
        $pagus->each(function ($pagu) {
            $usageByDetailId = [];

            $pagu->details->each(function ($detail) use ($pagu, &$usageByDetailId) {
                $terpakai = \App\Models\KegiatanAnggaran::where('pagu_detail_id', $detail->id)
                    ->whereHas('kegiatan', fn($q) => $q->where('tahun_anggaran', $pagu->tahun_anggaran))
                    ->sum('nominal_digunakan');

                $detail->terpakai  = (float) $terpakai;
                $detail->sisa      = (float) $detail->nominal - $terpakai;
                $usageByDetailId[$detail->id] = [
                    'terpakai' => $detail->terpakai,
                    'sisa'     => $detail->sisa,
                ];
            });

            $pagu->komponens->each(function ($komponen) use (&$usageByDetailId) {
                $komponen->details->each(function ($detail) use (&$usageByDetailId) {
                    $detail->terpakai = $usageByDetailId[$detail->id]['terpakai'] ?? 0;
                    $detail->sisa     = $usageByDetailId[$detail->id]['sisa'] ?? (float) $detail->nominal;
                });
            });

            $pagu->total_terpakai = $pagu->details->sum('terpakai');
            $pagu->sisa_pagu      = $pagu->details->sum('sisa');
        });

        return view('admin.pagu.index', compact('pagus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $input = $request->all();
        $input['komponen_anggaran'] = $this->normalizeKomponenAnggaran($input['komponen_anggaran'] ?? []);

        $validated = validator($input, [
            'kegiatan'                           => 'required|string|max:255',
            'tahun_anggaran'                    => 'required|digits:4|integer',
            'total_nominal'                     => 'required|numeric|min:0',
            'keterangan'                        => 'nullable|string',
            'komponen_anggaran'                 => 'required|array|min:1',
            'komponen_anggaran.*.id'            => 'nullable|integer',
            'komponen_anggaran.*.nama_komponen' => 'required|string|max:255',
            'komponen_anggaran.*.details'       => 'required|array|min:1',
            'komponen_anggaran.*.details.*.id'        => 'nullable|integer',
            'komponen_anggaran.*.details.*.nama_akun' => 'required|string|max:255',
            'komponen_anggaran.*.details.*.nominal'   => 'required|numeric|min:0',
        ])->validate();

        try {
            DB::transaction(function () use ($validated) {
                $pagu = Pagu::create(collect($validated)->only([
                    'kegiatan',
                    'tahun_anggaran',
                    'total_nominal',
                    'keterangan',
                ])->all());

                foreach ($validated['komponen_anggaran'] as $komponenData) {
                    $details = $komponenData['details'] ?? [];
                    unset($komponenData['details']);

                    $komponen = $pagu->komponens()->create($komponenData);
                    $komponen->details()->createMany(
                        collect($details)->map(fn ($detail) => [
                            'pagu_id'          => $pagu->id,
                            'nama_akun'        => $detail['nama_akun'],
                            'nominal'          => $detail['nominal'],
                        ])->all()
                    );
                }
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pagu: ' . $e->getMessage());
        }

        return redirect()->route('pagu.index')->with('success', 'Pagu berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pagu $pagu) {
        $input = $request->all();
        $input['komponen_anggaran'] = $this->normalizeKomponenAnggaran($input['komponen_anggaran'] ?? []);

        $validated = validator($input, [
            'kegiatan'                           => 'required|string|max:255',
            'tahun_anggaran'                    => 'required|digits:4|integer',
            'total_nominal'                     => 'required|numeric|min:0',
            'keterangan'                        => 'nullable|string',
            'komponen_anggaran'                 => 'required|array|min:1',
            'komponen_anggaran.*.id'            => 'nullable|integer',
            'komponen_anggaran.*.nama_komponen' => 'required|string|max:255',
            'komponen_anggaran.*.details'       => 'required|array|min:1',
            'komponen_anggaran.*.details.*.id'        => 'nullable|integer',
            'komponen_anggaran.*.details.*.nama_akun' => 'required|string|max:255',
            'komponen_anggaran.*.details.*.nominal'   => 'required|numeric|min:0',
        ])->validate();

        try {
            DB::transaction(function () use ($pagu, $validated) {
                $pagu->update(collect($validated)->only([
                    'kegiatan',
                    'tahun_anggaran',
                    'total_nominal',
                    'keterangan',
                ])->all());

                $this->syncKomponenAnggaran($pagu, $validated['komponen_anggaran']);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pagu: ' . $e->getMessage());
        }

        return redirect()->route('pagu.index')->with('success', 'Pagu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pagu $pagu) {
        $pagu->delete();
        return back();
    }

    private function normalizeKomponenAnggaran(array $komponens): array
    {
        return collect($komponens)
            ->map(function ($item) {
                if (is_string($item)) {
                    return [
                        'id'            => null,
                        'nama_komponen' => $this->resolveNamaKomponen(trim($item), []),
                        'details'       => [],
                    ];
                }

                $details = $this->normalizeDetails($item['details'] ?? []);

                return [
                    'id'            => blank($item['id'] ?? null) ? null : (int) $item['id'],
                    'nama_komponen' => $this->resolveNamaKomponen(
                        trim((string) ($item['nama_komponen'] ?? '')),
                        $details
                    ),
                    'details'       => $details,
                ];
            })
            ->filter(fn ($item) => filled($item['nama_komponen']) || ! empty($item['details']))
            ->values()
            ->all();
    }

    private function normalizeDetails(array $details): array
    {
        return collect($details)
            ->filter(function ($item) {
                $namaAkun = trim((string) ($item['nama_akun'] ?? ''));
                $nominal = (float) ($item['nominal'] ?? 0);

                return filled($namaAkun) || $nominal > 0;
            })
            ->map(fn ($item) => [
                'id'        => blank($item['id'] ?? null) ? null : (int) $item['id'],
                'nama_akun' => trim((string) ($item['nama_akun'] ?? '')),
                'nominal'   => (float) ($item['nominal'] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function resolveNamaKomponen(string $namaKomponen, array $details): string
    {
        if (filled($namaKomponen)) {
            return $namaKomponen;
        }

        return ! empty($details) ? 'Komponen Utama' : '';
    }

    private function syncKomponenAnggaran(Pagu $pagu, array $komponens): void
    {
        $existingKomponens = $pagu->komponens()->with('details')->get()->keyBy('id');
        $existingDetails = $pagu->details()->withCount('kegiatanAnggarans')->get()->keyBy('id');
        $retainedKomponenIds = [];
        $retainedDetailIds = [];

        foreach ($komponens as $komponenData) {
            $komponenId = $komponenData['id'] ?? null;
            $details = $komponenData['details'] ?? [];

            if ($komponenId !== null) {
                if (! $existingKomponens->has($komponenId)) {
                    throw ValidationException::withMessages([
                        'komponen_anggaran' => 'Data komponen pagu tidak valid. Muat ulang halaman lalu coba lagi.',
                    ]);
                }

                $komponen = $existingKomponens->get($komponenId);
                $komponen->update([
                    'nama_komponen' => $komponenData['nama_komponen'],
                ]);
            } else {
                $komponen = $pagu->komponens()->create([
                    'nama_komponen' => $komponenData['nama_komponen'],
                ]);
            }

            $retainedKomponenIds[] = $komponen->id;

            foreach ($details as $detailData) {
                $detailId = $detailData['id'] ?? null;

                if ($detailId !== null) {
                    if (! $existingDetails->has($detailId)) {
                        throw ValidationException::withMessages([
                            'komponen_anggaran' => 'Data detail pagu tidak valid. Muat ulang halaman lalu coba lagi.',
                        ]);
                    }

                    $detail = $existingDetails->get($detailId);
                    $detail->update([
                        'pagu_id'          => $pagu->id,
                        'pagu_komponen_id' => $komponen->id,
                        'nama_akun'        => $detailData['nama_akun'],
                        'nominal'          => $detailData['nominal'],
                    ]);
                } else {
                    $detail = $komponen->details()->create([
                        'pagu_id'   => $pagu->id,
                        'nama_akun' => $detailData['nama_akun'],
                        'nominal'   => $detailData['nominal'],
                    ]);
                }

                $retainedDetailIds[] = $detail->id;
            }
        }

        $detailsToDelete = $existingDetails->keys()->diff($retainedDetailIds);

        foreach ($detailsToDelete as $detailId) {
            $detail = $existingDetails->get($detailId);

            if (($detail->kegiatan_anggarans_count ?? 0) > 0) {
                throw ValidationException::withMessages([
                    'komponen_anggaran' => "Akun belanja '{$detail->nama_akun}' tidak bisa dihapus karena sudah dipakai pada kegiatan.",
                ]);
            }

            $detail->delete();
        }

        $komponensToDelete = $existingKomponens->keys()->diff($retainedKomponenIds);

        foreach ($komponensToDelete as $komponenId) {
            $komponen = $existingKomponens->get($komponenId);

            if ($pagu->details()->where('pagu_komponen_id', $komponenId)->exists()) {
                continue;
            }

            $komponen->delete();
        }
    }
}
