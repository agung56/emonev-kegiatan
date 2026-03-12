<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Pagu;
use App\Models\Sasaran;
use App\Models\KegiatanAnggaran;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $tahun     = date('Y');
        $bulanIni  = date('n');
        $today     = Carbon::today();

        // ── PENYERAPAN ANGGARAN (utama) ──────────────────────────────────
        $paguTahunIni = Pagu::where('tahun_anggaran', $tahun)->sum('total_nominal');

        $realisasiTahunIni = KegiatanAnggaran::whereHas('kegiatan', fn($q) =>
            $q->where('tahun_anggaran', $tahun)
        )->sum('nominal_digunakan');

        $sisaAnggaran   = $paguTahunIni - $realisasiTahunIni;
        $pctPenyerapan  = $paguTahunIni > 0
            ? round(($realisasiTahunIni / $paguTahunIni) * 100, 2)
            : 0;

        // ── PENYERAPAN PER BULAN (chart 12 bulan) ────────────────────────
        $penyerapanPerBulan = [];
        for ($m = 1; $m <= 12; $m++) {
            $val = KegiatanAnggaran::whereHas('kegiatan', fn($q) =>
                $q->where('tahun_anggaran', $tahun)
                  ->whereMonth('tanggal_mulai', $m)
            )->sum('nominal_digunakan');
            $penyerapanPerBulan[] = (float) $val;
        }

        // ── PENYERAPAN PER PAGU ───────────────────────────────────────────
        // Ambil realisasi per pagu_id via join langsung (hindari relasi di PaguDetail)
        $realisasiByPaguId = DB::table('kegiatan_anggarans as ka')
            ->join('kegiatans as k', 'k.id', '=', 'ka.kegiatan_id')
            ->join('pagu_details as pd', 'pd.id', '=', 'ka.pagu_detail_id')
            ->where('k.tahun_anggaran', $tahun)
            ->select('pd.pagu_id', DB::raw('SUM(ka.nominal_digunakan) as total_realisasi'))
            ->groupBy('pd.pagu_id')
            ->pluck('total_realisasi', 'pd.pagu_id');

        $penyerapanPerPagu = Pagu::where('tahun_anggaran', $tahun)
            ->get()
            ->map(function ($p) use ($realisasiByPaguId) {
                $realisasi = (float) ($realisasiByPaguId[$p->id] ?? 0);
                $sisa      = $p->total_nominal - $realisasi;
                $pct       = $p->total_nominal > 0
                    ? round(($realisasi / $p->total_nominal) * 100, 1)
                    : 0;
                return [
                    'kegiatan'  => $p->kegiatan,
                    'pagu'      => $p->total_nominal,
                    'realisasi' => $realisasi,
                    'sisa'      => $sisa,
                    'pct'       => $pct,
                ];
            });

        // ── STATISTIK KEGIATAN ────────────────────────────────────────────
        $totalKegiatan      = Kegiatan::where('tahun_anggaran', $tahun)->count();
        $kegiatanBulanIni   = Kegiatan::where('tahun_anggaran', $tahun)
                                ->whereMonth('tanggal_mulai', $bulanIni)->count();
        $kegiatanBerjalan   = Kegiatan::where('tahun_anggaran', $tahun)
                                ->where('tanggal_mulai', '<=', $today)
                                ->where('tanggal_selesai', '>=', $today)->count();
        $kegiatanSelesai    = Kegiatan::where('tahun_anggaran', $tahun)
                                ->where('tanggal_selesai', '<', $today)->count();
        $kegiatanMendatang  = Kegiatan::where('tahun_anggaran', $tahun)
                                ->where('tanggal_mulai', '>', $today)->count();

        // ── KEGIATAN TERBARU ──────────────────────────────────────────────
        $kegiatanTerbaru = Kegiatan::with(['sasaran', 'pagu', 'anggarans', 'createdBy.subBagian'])
            ->where('tahun_anggaran', $tahun)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ── KEGIATAN MENDATANG ────────────────────────────────────────────
        $kegiatanUpcoming = Kegiatan::with(['sasaran'])
            ->where('tahun_anggaran', $tahun)
            ->where('tanggal_mulai', '>', $today)
            ->orderBy('tanggal_mulai', 'asc')
            ->limit(5)
            ->get();

        // ── PENYERAPAN PER KEPEMILIKAN ────────────────────────────────────
        foreach (['lembaga', 'sekretariat'] as $kep) {
            $paguKep[$kep] = Pagu::whereHas('kegiatans', fn($q) =>
                $q->where('tahun_anggaran', $tahun)->where('kepemilikan', $kep)
            )->sum('total_nominal');

            $realisasiKep[$kep] = KegiatanAnggaran::whereHas('kegiatan', fn($q) =>
                $q->where('tahun_anggaran', $tahun)->where('kepemilikan', $kep)
            )->sum('nominal_digunakan');
        }

        // ── SASARAN & INDIKATOR ───────────────────────────────────────────
        $totalSasaran = Sasaran::where('tahun_anggaran', $tahun)->where('is_aktif', true)->count();

        // ── PENYERAPAN PER SUB BAGIAN ─────────────────────────────────────
        // Jalur: sub_bagians → users (sub_bagian_id) → kegiatans (created_by) → kegiatan_anggarans
        $penyerapanPerSubBagian = DB::table('sub_bagians as sb')
            ->leftJoin('users as u', 'u.sub_bagian_id', '=', 'sb.id')
            ->leftJoin('kegiatans as k', function ($join) use ($tahun) {
                $join->on('k.created_by', '=', 'u.id')
                     ->where('k.tahun_anggaran', '=', $tahun);
            })
            ->leftJoin('kegiatan_anggarans as ka', 'ka.kegiatan_id', '=', 'k.id')
            ->select(
                'sb.id',
                'sb.nama_sub_bagian',
                DB::raw('COUNT(DISTINCT k.id) as total_kegiatan'),
                DB::raw('COALESCE(SUM(ka.nominal_digunakan), 0) as total_realisasi')
            )
            ->groupBy('sb.id', 'sb.nama_sub_bagian')
            ->orderBy('total_realisasi', 'desc')
            ->get();

        $maxRealisasiSubBagian = $penyerapanPerSubBagian->max('total_realisasi') ?: 1;

        return view('admin.index', compact(
            'user', 'tahun', 'today',
            'paguTahunIni', 'realisasiTahunIni', 'sisaAnggaran', 'pctPenyerapan',
            'penyerapanPerBulan', 'penyerapanPerPagu',
            'totalKegiatan', 'kegiatanBulanIni', 'kegiatanBerjalan',
            'kegiatanSelesai', 'kegiatanMendatang',
            'kegiatanTerbaru', 'kegiatanUpcoming',
            'paguKep', 'realisasiKep',
            'totalSasaran',
            'penyerapanPerSubBagian', 'maxRealisasiSubBagian'
        ));
    }
}
