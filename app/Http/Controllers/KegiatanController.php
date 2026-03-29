<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\KegiatanAnggaran;
use App\Models\KegiatanDokumen;
use App\Models\Pagu;
use App\Models\Sasaran;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::with(['pagu', 'sasaran', 'indikators', 'anggarans', 'createdBy.subBagian', 'subBagianPelaksana'])
            ->latest()
            ->paginate(10);

        $tahuns = Kegiatan::select('tahun_anggaran')
            ->distinct()
            ->orderBy('tahun_anggaran', 'desc')
            ->pluck('tahun_anggaran');

        return view('admin.kegiatans.index', compact('kegiatans', 'tahuns'));
    }

    public function create()
    {
        $pagus   = Pagu::with('details.komponen')->get();
        $sasarans = Sasaran::with('indikators')->where('is_aktif', true)->get();
        $subBagians = SubBagian::orderBy('nama_sub_bagian')->get();
        return view('admin.kegiatans.create', compact('pagus', 'sasarans', 'subBagians'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['anggaran'] = $this->normalizeAnggaran($input['anggaran'] ?? []);

        $validator = Validator::make($input, [
            'tahun_anggaran'            => 'required|digits:4|integer',
            'kepemilikan'               => 'required|in:lembaga,sekretariat',
            'sub_bagian_id'             => 'nullable|exists:sub_bagians,id',
            'pagu_id'                   => 'nullable|exists:pagus,id',
            'sasaran_id'                => 'required|exists:sasarans,id',
            'nama_kegiatan'             => 'required|string|max:255',
            'lokus'                     => 'nullable|string|max:255',
            'tanggal_mulai'             => 'required|date',
            'tanggal_selesai'           => 'required|date|after_or_equal:tanggal_mulai',
            'output_kegiatan'           => 'required|string',
            'kendala_kegiatan'          => 'required|string',
            'indikator_ids'             => 'required|array|min:1',
            'indikator_ids.*'           => 'exists:indikators,id',
            'anggaran'                  => 'nullable|array',
            'anggaran.*.pagu_detail_id' => 'required|exists:pagu_details,id',
            'anggaran.*.nominal'        => 'required|numeric|gt:0',
            'dokumen'                   => 'required|array|min:1',
            'dokumen.*'                 => 'required|file|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx|max:5120',
        ]);
        $validator->after(function ($validator) use ($input) {
            if (!empty($input['anggaran']) && empty($input['pagu_id'])) {
                $validator->errors()->add('pagu_id', 'Pagu anggaran wajib dipilih jika ada pengeluaran anggaran.');
            }

            if (auth()->user()?->role === 'admin' && empty($input['sub_bagian_id'])) {
                $validator->errors()->add('sub_bagian_id', 'Sub Bagian pelaksana wajib dipilih untuk akun admin.');
            }
        });
        $validated = $validator->validate();

        DB::beginTransaction();
        try {
            $kegiatan = Kegiatan::create([
                'tahun_anggaran'   => $validated['tahun_anggaran'],
                'kepemilikan'      => $validated['kepemilikan'],
                'pagu_id'          => $validated['pagu_id'] ?? null,
                'sasaran_id'       => $validated['sasaran_id'],
                'nama_kegiatan'    => $validated['nama_kegiatan'],
                'lokus'            => $validated['lokus'] ?? null,
                'tanggal_mulai'    => $validated['tanggal_mulai'],
                'tanggal_selesai'  => $validated['tanggal_selesai'],
                'output_kegiatan'  => $validated['output_kegiatan'] ?? null,
                'kendala_kegiatan' => $validated['kendala_kegiatan'] ?? null,
                'created_by'       => auth()->id(),
                'sub_bagian_id'    => auth()->user()?->role === 'admin'
                    ? ($validated['sub_bagian_id'] ?? null)
                    : auth()->user()?->sub_bagian_id,
            ]);

            // Sync indikators (many-to-many)
            $kegiatan->indikators()->sync($request->indikator_ids);

            // Save anggaran per akun
            if (!empty($validated['anggaran'])) {
                foreach ($validated['anggaran'] as $item) {
                    KegiatanAnggaran::create([
                        'kegiatan_id'       => $kegiatan->id,
                        'pagu_detail_id'    => $item['pagu_detail_id'],
                        'nominal_digunakan' => $item['nominal'],
                    ]);
                }
            }

            // Upload dokumen
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $file) {
                    $ext  = strtolower($file->getClientOriginalExtension());
                    $tipe = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image'
                          : ($ext === 'pdf' ? 'pdf'
                          : (in_array($ext, ['doc', 'docx']) ? 'word' : 'excel'));

                    $path = $file->store('kegiatan_dokumen', 'public');

                    KegiatanDokumen::create([
                        'kegiatan_id' => $kegiatan->id,
                        'nama_file'   => $file->getClientOriginalName(),
                        'path_file'   => $path,
                        'tipe_file'   => $tipe,
                        'ukuran_file' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('kegiatans.index')
                ->with('success', 'Kegiatan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load([
            'pagu.details.komponen',
            'sasaran',
            'indikators',
            'anggarans.paguDetail.komponen',
            'dokumens',
            'createdBy.subBagian',
            'subBagianPelaksana',
        ]);
        return view('admin.kegiatans.show', compact('kegiatan'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        $kegiatan->load(['indikators', 'anggarans.paguDetail.komponen', 'dokumens', 'pagu.details.komponen', 'sasaran.indikators', 'subBagianPelaksana']);
        $pagus    = Pagu::with('details.komponen')->get();
        $sasarans = Sasaran::with('indikators')->where('is_aktif', true)->get();
        $subBagians = SubBagian::orderBy('nama_sub_bagian')->get();
        return view('admin.kegiatans.edit', compact('kegiatan', 'pagus', 'sasarans', 'subBagians'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $input = $request->all();
        $input['anggaran'] = $this->normalizeAnggaran($input['anggaran'] ?? []);

        $validator = Validator::make($input, [
            'tahun_anggaran'            => 'required|digits:4|integer',
            'kepemilikan'               => 'required|in:lembaga,sekretariat',
            'sub_bagian_id'             => 'nullable|exists:sub_bagians,id',
            'pagu_id'                   => 'nullable|exists:pagus,id',
            'sasaran_id'                => 'required|exists:sasarans,id',
            'nama_kegiatan'             => 'required|string|max:255',
            'lokus'                     => 'nullable|string|max:255',
            'tanggal_mulai'             => 'required|date',
            'tanggal_selesai'           => 'required|date|after_or_equal:tanggal_mulai',
            'output_kegiatan'           => 'required|string',
            'kendala_kegiatan'          => 'required|string',
            'indikator_ids'             => 'required|array|min:1',
            'indikator_ids.*'           => 'exists:indikators,id',
            'anggaran'                  => 'nullable|array',
            'anggaran.*.pagu_detail_id' => 'required|exists:pagu_details,id',
            'anggaran.*.nominal'        => 'required|numeric|gt:0',
            'hapus_dokumen'             => 'nullable|array',
            'hapus_dokumen.*'           => 'exists:kegiatan_dokumens,id',
            'dokumen.*'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx|max:5120',
        ]);
        $validator->after(function ($validator) use ($request, $kegiatan, $input) {
            if (!empty($input['anggaran']) && empty($input['pagu_id'])) {
                $validator->errors()->add('pagu_id', 'Pagu anggaran wajib dipilih jika ada pengeluaran anggaran.');
            }

            if (auth()->user()?->role === 'admin' && empty($input['sub_bagian_id'])) {
                $validator->errors()->add('sub_bagian_id', 'Sub Bagian pelaksana wajib dipilih untuk akun admin.');
            }

            $hapusDokumen = $request->input('hapus_dokumen', []);
            $sisaDokumenLama = $kegiatan->dokumens()->whereNotIn('id', $hapusDokumen)->count();
            $dokumenBaru = is_array($request->file('dokumen')) ? count(array_filter($request->file('dokumen'))) : 0;

            if (($sisaDokumenLama + $dokumenBaru) < 1) {
                $validator->errors()->add('dokumen', 'Minimal satu dokumen kegiatan wajib tersedia.');
            }
        });
        $validated = $validator->validate();

        DB::beginTransaction();
        try {
            $kegiatan->update([
                'tahun_anggaran'   => $validated['tahun_anggaran'],
                'kepemilikan'      => $validated['kepemilikan'],
                'pagu_id'          => $validated['pagu_id'] ?? null,
                'sasaran_id'       => $validated['sasaran_id'],
                'nama_kegiatan'    => $validated['nama_kegiatan'],
                'lokus'            => $validated['lokus'] ?? null,
                'tanggal_mulai'    => $validated['tanggal_mulai'],
                'tanggal_selesai'  => $validated['tanggal_selesai'],
                'output_kegiatan'  => $validated['output_kegiatan'] ?? null,
                'kendala_kegiatan' => $validated['kendala_kegiatan'] ?? null,
                'sub_bagian_id'    => auth()->user()?->role === 'admin'
                    ? ($validated['sub_bagian_id'] ?? null)
                    : (auth()->user()?->sub_bagian_id ?? $kegiatan->sub_bagian_id),
            ]);

            // Sync indikators
            $kegiatan->indikators()->sync($request->indikator_ids);

            // Update anggaran: hapus semua lalu insert ulang
            $kegiatan->anggarans()->delete();
            if (!empty($validated['anggaran'])) {
                foreach ($validated['anggaran'] as $item) {
                    KegiatanAnggaran::create([
                        'kegiatan_id'       => $kegiatan->id,
                        'pagu_detail_id'    => $item['pagu_detail_id'],
                        'nominal_digunakan' => $item['nominal'],
                    ]);
                }
            }

            // Hapus dokumen yang dicentang
            if ($request->filled('hapus_dokumen')) {
                $toDel = KegiatanDokumen::whereIn('id', $request->hapus_dokumen)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->get();
                foreach ($toDel as $dok) {
                    Storage::disk('public')->delete($dok->path_file);
                    $dok->delete();
                }
            }

            // Upload dokumen baru
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $file) {
                    $ext  = strtolower($file->getClientOriginalExtension());
                    $tipe = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image'
                          : ($ext === 'pdf' ? 'pdf'
                          : (in_array($ext, ['doc', 'docx']) ? 'word' : 'excel'));

                    $path = $file->store('kegiatan_dokumen', 'public');

                    KegiatanDokumen::create([
                        'kegiatan_id' => $kegiatan->id,
                        'nama_file'   => $file->getClientOriginalName(),
                        'path_file'   => $path,
                        'tipe_file'   => $tipe,
                        'ukuran_file' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('kegiatans.show', $kegiatan->id)
                ->with('success', 'Kegiatan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal memperbarui: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Kegiatan $kegiatan)
    {
        DB::beginTransaction();
        try {
            // Hapus semua file fisik
            foreach ($kegiatan->dokumens as $dok) {
                Storage::disk('public')->delete($dok->path_file);
            }
            $kegiatan->delete(); // cascade handles relasi lain
            DB::commit();
            return redirect()->route('kegiatans.index')
                ->with('success', 'Kegiatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ── AJAX Endpoints ───────────────────────────────────────────────────────

    public function getIndikators(int $sasaranId)
    {
        $indikators = \App\Models\Indikator::where('sasaran_id', $sasaranId)
            ->select('id', 'nama_indikator', 'target', 'satuan')
            ->get();
        return response()->json($indikators);
    }

    public function getPaguDetails(int $paguId)
    {
        $excludeKegiatanId = request()->query('exclude_kegiatan');

        // Ambil pagu beserta tahun_anggaran-nya untuk filter
        $pagu = \App\Models\Pagu::findOrFail($paguId);
        $tahunAnggaran = $pagu->tahun_anggaran;

        $details = \App\Models\PaguDetail::where('pagu_id', $paguId)
            ->with('komponen:id,nama_komponen')
            ->select('id', 'pagu_komponen_id', 'nama_akun', 'nominal')
            ->get()
            ->map(function ($d) use ($excludeKegiatanId, $tahunAnggaran) {
                // Hitung total terpakai dari SEMUA kegiatan yang:
                // 1. Pakai pagu_detail ini
                // 2. Tahun anggaran cocok dengan pagu
                // 3. Exclude kegiatan yang sedang diedit (agar tidak dobel)
                $sudahTerpakai = \App\Models\KegiatanAnggaran::where('pagu_detail_id', $d->id)
                    ->whereHas('kegiatan', function ($q) use ($tahunAnggaran, $excludeKegiatanId) {
                        $q->where('tahun_anggaran', $tahunAnggaran);
                        if ($excludeKegiatanId) {
                            $q->where('id', '!=', $excludeKegiatanId);
                        }
                    })
                    ->sum('nominal_digunakan');

                $d->sudah_terpakai = (float) $sudahTerpakai;
                $d->sisa_tersedia  = (float) $d->nominal - $sudahTerpakai;
                $d->nama_komponen  = $d->komponen?->nama_komponen;
                return $d;
            });

        return response()->json($details);
    }

    private function normalizeAnggaran(array $anggaran): array
    {
        return collect($anggaran)
            ->filter(function ($item) {
                $paguDetailId = $item['pagu_detail_id'] ?? null;
                $nominal = (float) ($item['nominal'] ?? 0);

                return filled($paguDetailId) || $nominal > 0;
            })
            ->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'pagu_detail_id' => $item['pagu_detail_id'] ?? null,
                    'nominal' => (float) ($item['nominal'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }
}
