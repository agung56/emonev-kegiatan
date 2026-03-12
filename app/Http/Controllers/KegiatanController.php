<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\KegiatanAnggaran;
use App\Models\KegiatanDokumen;
use App\Models\Pagu;
use App\Models\Sasaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::with(['pagu', 'sasaran', 'indikators', 'anggarans', 'createdBy.subBagian'])
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
        $pagus   = Pagu::with('details')->get();
        $sasarans = Sasaran::with('indikators')->where('is_aktif', true)->get();
        return view('admin.kegiatans.create', compact('pagus', 'sasarans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_anggaran'            => 'required|digits:4|integer',
            'kepemilikan'               => 'required|in:lembaga,sekretariat',
            'pagu_id'                   => 'required|exists:pagus,id',
            'sasaran_id'                => 'required|exists:sasarans,id',
            'nama_kegiatan'             => 'required|string|max:255',
            'lokus'                     => 'nullable|string|max:255',
            'tanggal_mulai'             => 'required|date',
            'tanggal_selesai'           => 'required|date|after_or_equal:tanggal_mulai',
            'output_kegiatan'           => 'nullable|string',
            'kendala_kegiatan'          => 'nullable|string',
            'indikator_ids'             => 'required|array|min:1',
            'indikator_ids.*'           => 'exists:indikators,id',
            'anggaran'                  => 'nullable|array',
            'anggaran.*.pagu_detail_id' => 'required_with:anggaran|exists:pagu_details,id',
            'anggaran.*.nominal'        => 'required_with:anggaran|numeric|min:0',
            'dokumen.*'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $kegiatan = Kegiatan::create([
                'tahun_anggaran'   => $validated['tahun_anggaran'],
                'kepemilikan'      => $validated['kepemilikan'],
                'pagu_id'          => $validated['pagu_id'],
                'sasaran_id'       => $validated['sasaran_id'],
                'nama_kegiatan'    => $validated['nama_kegiatan'],
                'lokus'            => $validated['lokus'] ?? null,
                'tanggal_mulai'    => $validated['tanggal_mulai'],
                'tanggal_selesai'  => $validated['tanggal_selesai'],
                'output_kegiatan'  => $validated['output_kegiatan'] ?? null,
                'kendala_kegiatan' => $validated['kendala_kegiatan'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            // Sync indikators (many-to-many)
            $kegiatan->indikators()->sync($request->indikator_ids);

            // Save anggaran per akun
            if ($request->filled('anggaran')) {
                foreach ($request->anggaran as $item) {
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
            'pagu.details',
            'sasaran',
            'indikators',
            'anggarans.paguDetail',
            'dokumens',
            'createdBy.subBagian',
        ]);
        return view('admin.kegiatans.show', compact('kegiatan'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        $kegiatan->load(['indikators', 'anggarans', 'dokumens', 'pagu.details', 'sasaran.indikators']);
        $pagus    = Pagu::with('details')->get();
        $sasarans = Sasaran::with('indikators')->where('is_aktif', true)->get();
        return view('admin.kegiatans.edit', compact('kegiatan', 'pagus', 'sasarans'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'tahun_anggaran'            => 'required|digits:4|integer',
            'kepemilikan'               => 'required|in:lembaga,sekretariat',
            'pagu_id'                   => 'required|exists:pagus,id',
            'sasaran_id'                => 'required|exists:sasarans,id',
            'nama_kegiatan'             => 'required|string|max:255',
            'lokus'                     => 'nullable|string|max:255',
            'tanggal_mulai'             => 'required|date',
            'tanggal_selesai'           => 'required|date|after_or_equal:tanggal_mulai',
            'output_kegiatan'           => 'nullable|string',
            'kendala_kegiatan'          => 'nullable|string',
            'indikator_ids'             => 'required|array|min:1',
            'indikator_ids.*'           => 'exists:indikators,id',
            'anggaran'                  => 'nullable|array',
            'anggaran.*.pagu_detail_id' => 'required_with:anggaran|exists:pagu_details,id',
            'anggaran.*.nominal'        => 'required_with:anggaran|numeric|min:0',
            'hapus_dokumen'             => 'nullable|array',
            'hapus_dokumen.*'           => 'exists:kegiatan_dokumens,id',
            'dokumen.*'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $kegiatan->update([
                'tahun_anggaran'   => $validated['tahun_anggaran'],
                'kepemilikan'      => $validated['kepemilikan'],
                'pagu_id'          => $validated['pagu_id'],
                'sasaran_id'       => $validated['sasaran_id'],
                'nama_kegiatan'    => $validated['nama_kegiatan'],
                'lokus'            => $validated['lokus'] ?? null,
                'tanggal_mulai'    => $validated['tanggal_mulai'],
                'tanggal_selesai'  => $validated['tanggal_selesai'],
                'output_kegiatan'  => $validated['output_kegiatan'] ?? null,
                'kendala_kegiatan' => $validated['kendala_kegiatan'] ?? null,
            ]);

            // Sync indikators
            $kegiatan->indikators()->sync($request->indikator_ids);

            // Update anggaran: hapus semua lalu insert ulang
            $kegiatan->anggarans()->delete();
            if ($request->filled('anggaran')) {
                foreach ($request->anggaran as $item) {
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
            ->select('id', 'nama_indikator')
            ->get();
        return response()->json($indikators);
    }

    public function getPaguDetails(int $paguId)
    {
        $details = \App\Models\PaguDetail::where('pagu_id', $paguId)
            ->select('id', 'nama_akun', 'nominal')
            ->get();
        return response()->json($details);
    }
}
