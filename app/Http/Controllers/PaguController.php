<?php

namespace App\Http\Controllers;

use App\Services\RkksExcelImportService;
use App\Models\Pagu;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaguController extends Controller
{
    private function ensureAdminAccess(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

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
            $pagu->can_be_deleted = ! $pagu->kegiatans()->exists()
                && ! $pagu->details()->whereHas('kegiatanAnggarans')->exists();
        });

        $importPreview = $this->prepareImportPreview(session('pagu_import_preview'));

        return view('admin.pagu.index', compact('pagus', 'importPreview'));
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
        $this->ensureAdminAccess();

        $validated = $this->validatePaguPayload($request->all());

        try {
            DB::transaction(function () use ($validated) {
                $this->createPaguFromValidated($validated);
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
        $this->ensureAdminAccess();

        $validated = $this->validatePaguPayload($request->all());

        try {
            DB::transaction(function () use ($pagu, $validated) {
                $this->updatePaguFromValidated($pagu, $validated);
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
        $this->ensureAdminAccess();

        $isUsedByKegiatan = $pagu->kegiatans()->exists();
        $isUsedByAnggaran = $pagu->details()->whereHas('kegiatanAnggarans')->exists();

        if ($isUsedByKegiatan || $isUsedByAnggaran) {
            return back()->with('error', 'Pagu tidak bisa dihapus karena sudah dipakai pada data kegiatan atau anggaran.');
        }

        try {
            $pagu->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Pagu tidak bisa dihapus karena masih terhubung dengan data lain.');
        }

        return back()->with('success', 'Pagu berhasil dihapus.');
    }

    public function previewRkksImport(Request $request, RkksExcelImportService $service)
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'rkks_excel' => 'required|file|mimes:xlsx,csv,txt|max:20480',
        ]);

        try {
            $preview = $service->parse(
                $validated['rkks_excel']->getRealPath(),
                $validated['rkks_excel']->getClientOriginalName(),
            );

            session(['pagu_import_preview' => $preview]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca RKKS Excel: ' . $e->getMessage());
        }

        return redirect()->route('pagu.index')->with('success', 'Preview import RKKS Excel berhasil dibuat.');
    }

    public function applyRkksImport(Request $request)
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'mode' => 'required|in:create,sync',
        ]);

        $preview = session('pagu_import_preview');

        if (! is_array($preview) || empty($preview['programs'])) {
            return redirect()->route('pagu.index')->with('error', 'Preview import RKKS Excel belum tersedia.');
        }

        try {
            $validatedPrograms = collect($preview['programs'])
                ->map(fn (array $program) => $this->validatePaguPayload($program))
                ->all();

            DB::transaction(function () use ($validatedPrograms, $validated) {
                foreach ($validatedPrograms as $programData) {
                    $existing = $this->findExistingPagu($programData['program'], (int) $programData['tahun_anggaran']);

                    if ($validated['mode'] === 'create') {
                        if ($existing) {
                            throw ValidationException::withMessages([
                                'rkks_excel' => "Program '{$programData['program']}' tahun {$programData['tahun_anggaran']} sudah ada. Gunakan sinkronisasi.",
                            ]);
                        }

                        $this->createPaguFromValidated($programData);
                        continue;
                    }

                    if ($existing) {
                        $this->updatePaguFromValidated($existing, $programData);
                    } else {
                        $this->createPaguFromValidated($programData);
                    }
                }
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menerapkan import RKKS Excel: ' . $e->getMessage());
        }

        session()->forget('pagu_import_preview');

        return redirect()->route('pagu.index')->with('success', 'Import RKKS Excel berhasil diterapkan.');
    }

    public function clearRkksImport()
    {
        $this->ensureAdminAccess();

        session()->forget('pagu_import_preview');

        return redirect()->route('pagu.index')->with('success', 'Preview import RKKS Excel dibersihkan.');
    }

    private function validatePaguPayload(array $input): array
    {
        $input['komponen_anggaran'] = $this->normalizeKomponenAnggaran($input['komponen_anggaran'] ?? []);

        return validator($input, [
            'program'                            => 'required|string|max:255',
            'tahun_anggaran'                     => 'required|digits:4|integer',
            'total_nominal'                      => 'required|numeric|min:0',
            'keterangan'                         => 'nullable|string',
            'komponen_anggaran'                  => 'required|array|min:1',
            'komponen_anggaran.*.id'             => 'nullable|integer',
            'komponen_anggaran.*.nama_kegiatan'  => 'required|string|max:255',
            'komponen_anggaran.*.details'        => 'required|array|min:1',
            'komponen_anggaran.*.details.*.id'   => 'nullable|integer',
            'komponen_anggaran.*.details.*.ro'   => 'required|string|max:255',
            'komponen_anggaran.*.details.*.komponen_label' => 'required|string|max:255',
            'komponen_anggaran.*.details.*.sub_komponen'   => 'required|string|max:255',
            'komponen_anggaran.*.details.*.detail'         => 'required|string|max:255',
            'komponen_anggaran.*.details.*.nominal'        => 'required|numeric|min:0',
        ])->validate();
    }

    private function createPaguFromValidated(array $validated): Pagu
    {
        $pagu = Pagu::create(collect($validated)->only([
            'program',
            'tahun_anggaran',
            'total_nominal',
            'keterangan',
        ])->merge([
            'kegiatan' => $validated['program'],
        ])->all());

        foreach ($validated['komponen_anggaran'] as $komponenData) {
            $details = $komponenData['details'] ?? [];
            unset($komponenData['details']);

            $komponen = $pagu->komponens()->create([
                'nama_kegiatan' => $komponenData['nama_kegiatan'],
                'nama_komponen' => $komponenData['nama_kegiatan'],
            ]);

            $komponen->details()->createMany(
                collect($details)->map(fn ($detail) => [
                    'pagu_id'        => $pagu->id,
                    'ro'             => $detail['ro'],
                    'komponen_label' => $detail['komponen_label'],
                    'sub_komponen'   => $detail['sub_komponen'],
                    'detail'         => $detail['detail'],
                    'nama_akun'      => $detail['detail'],
                    'nominal'        => $detail['nominal'],
                ])->all()
            );
        }

        return $pagu;
    }

    private function updatePaguFromValidated(Pagu $pagu, array $validated): Pagu
    {
        $pagu->update(collect($validated)->only([
            'program',
            'tahun_anggaran',
            'total_nominal',
            'keterangan',
        ])->merge([
            'kegiatan' => $validated['program'],
        ])->all());

        $this->syncKomponenAnggaran($pagu, $validated['komponen_anggaran']);

        return $pagu;
    }

    private function normalizeKomponenAnggaran(array $komponens): array
    {
        return collect($komponens)
            ->map(function ($item) {
                if (is_string($item)) {
                    return [
                        'id'            => null,
                        'nama_kegiatan' => $this->resolveNamaKegiatan(trim($item), []),
                        'ro'            => '',
                        'komponen_label' => '',
                        'details'       => [],
                    ];
                }

                $ro = trim((string) ($item['ro'] ?? ''));
                $komponenLabel = trim((string) ($item['komponen_label'] ?? ''));

                $details = collect([
                    ...($item['details'] ?? []),
                    ...$this->normalizeSubKomponens($item['sub_komponens'] ?? [], $ro, $komponenLabel),
                ])->values()->all();

                $details = $this->normalizeDetails($details);

                return [
                    'id'            => blank($item['id'] ?? null) ? null : (int) $item['id'],
                    'nama_kegiatan' => $this->resolveNamaKegiatan(
                        trim((string) ($item['nama_kegiatan'] ?? '')),
                        $details
                    ),
                    'ro'            => $ro,
                    'komponen_label' => $komponenLabel,
                    'details'       => $details,
                ];
            })
            ->filter(fn ($item) => filled($item['nama_kegiatan']) || ! empty($item['details']))
            ->values()
            ->all();
    }

    private function normalizeDetails(array $details): array
    {
        return collect($details)
            ->filter(function ($item) {
                $ro = trim((string) ($item['ro'] ?? ''));
                $komponenLabel = trim((string) ($item['komponen_label'] ?? ''));
                $subKomponen = trim((string) ($item['sub_komponen'] ?? ''));
                $detail = trim((string) ($item['detail'] ?? ''));
                $nominal = (float) ($item['nominal'] ?? 0);

                return filled($ro) || filled($komponenLabel) || filled($subKomponen) || filled($detail) || $nominal > 0;
            })
            ->map(fn ($item) => [
                'id'              => blank($item['id'] ?? null) ? null : (int) $item['id'],
                'ro'              => trim((string) ($item['ro'] ?? '')),
                'komponen_label'  => trim((string) ($item['komponen_label'] ?? '')),
                'sub_komponen'    => trim((string) ($item['sub_komponen'] ?? '')),
                'detail'          => trim((string) ($item['detail'] ?? '')),
                'nominal'         => (float) ($item['nominal'] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function normalizeSubKomponens(array $subKomponens, string $ro = '', string $komponenLabel = ''): array
    {
        return collect($subKomponens)
            ->flatMap(function ($item) use ($ro, $komponenLabel) {
                $itemRo = trim((string) ($item['ro'] ?? $ro));
                $itemKomponenLabel = trim((string) ($item['komponen_label'] ?? $komponenLabel));
                $subKomponen = trim((string) ($item['sub_komponen'] ?? ''));

                return collect($item['details'] ?? [])->map(fn ($detail) => [
                    'id' => blank($detail['id'] ?? null) ? null : (int) $detail['id'],
                    'ro' => $itemRo,
                    'komponen_label' => $itemKomponenLabel,
                    'sub_komponen' => $subKomponen,
                    'detail' => trim((string) ($detail['detail'] ?? '')),
                    'nominal' => (float) ($detail['nominal'] ?? 0),
                ]);
            })
            ->values()
            ->all();
    }

    private function resolveNamaKegiatan(string $namaKegiatan, array $details): string
    {
        if (filled($namaKegiatan)) {
            return $namaKegiatan;
        }

        return ! empty($details) ? 'Kegiatan Utama' : '';
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
                    'nama_kegiatan' => $komponenData['nama_kegiatan'],
                    'nama_komponen' => $komponenData['nama_kegiatan'],
                ]);
            } else {
                $komponen = $pagu->komponens()->create([
                    'nama_kegiatan' => $komponenData['nama_kegiatan'],
                    'nama_komponen' => $komponenData['nama_kegiatan'],
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
                        'ro'               => $detailData['ro'],
                        'komponen_label'   => $detailData['komponen_label'],
                        'sub_komponen'     => $detailData['sub_komponen'],
                        'detail'           => $detailData['detail'],
                        'nama_akun'        => $detailData['detail'],
                        'nominal'          => $detailData['nominal'],
                    ]);
                } else {
                    $detail = $komponen->details()->create([
                        'pagu_id'         => $pagu->id,
                        'ro'              => $detailData['ro'],
                        'komponen_label'  => $detailData['komponen_label'],
                        'sub_komponen'    => $detailData['sub_komponen'],
                        'detail'          => $detailData['detail'],
                        'nama_akun'       => $detailData['detail'],
                        'nominal'         => $detailData['nominal'],
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
                    'komponen_anggaran' => "Detail anggaran '{$detail->detail_label}' tidak bisa dihapus karena sudah dipakai pada kegiatan.",
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

    private function findExistingPagu(string $program, int $tahunAnggaran): ?Pagu
    {
        return Pagu::query()
            ->where('tahun_anggaran', $tahunAnggaran)
            ->get()
            ->first(function (Pagu $pagu) use ($program) {
                return mb_strtolower(trim($pagu->program_label)) === mb_strtolower(trim($program));
            });
    }

    private function prepareImportPreview(?array $preview): ?array
    {
        if (! is_array($preview) || empty($preview['programs'])) {
            return null;
        }

        $tahunAnggaran = (int) ($preview['tahun_anggaran'] ?? now()->year);
        $existingPagus = Pagu::where('tahun_anggaran', $tahunAnggaran)->get();

        $programs = collect($preview['programs'])
            ->map(function (array $program) use ($existingPagus) {
                $matched = $existingPagus->first(function (Pagu $pagu) use ($program) {
                    return mb_strtolower(trim($pagu->program_label)) === mb_strtolower(trim($program['program'] ?? ''));
                });

                $kegiatanCount = collect($program['komponen_anggaran'] ?? [])->count();
                $detailCount = collect($program['komponen_anggaran'] ?? [])
                    ->sum(fn ($kegiatan) => collect($kegiatan['sub_komponens'] ?? [])
                        ->sum(fn ($subKomponen) => count($subKomponen['details'] ?? [])));

                return array_merge($program, [
                    'matched_pagu_id' => $matched?->id,
                    'matched_total_nominal' => $matched?->total_nominal,
                    'match_status' => $matched ? 'existing' : 'new',
                    'kegiatan_count' => $kegiatanCount,
                    'detail_count' => $detailCount,
                ]);
            })
            ->values()
            ->all();

        return array_merge($preview, [
            'tahun_anggaran' => $tahunAnggaran,
            'program_count' => count($programs),
            'kegiatan_count' => collect($programs)->sum('kegiatan_count'),
            'detail_count' => collect($programs)->sum('detail_count'),
            'programs' => $programs,
        ]);
    }
}




