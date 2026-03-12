<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Pagu;
use App\Models\Sasaran;
use App\Models\KegiatanAnggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $tahunSekarang = (int) date('Y');
        $tahunList = collect()
            ->merge(Kegiatan::query()->distinct()->pluck('tahun_anggaran'))
            ->merge(Pagu::query()->distinct()->pluck('tahun_anggaran'))
            ->merge(Sasaran::query()->distinct()->pluck('tahun_anggaran'))
            ->filter(fn ($tahun) => filled($tahun))
            ->map(fn ($tahun) => (int) $tahun)
            ->unique()
            ->sortDesc()
            ->values();

        $requestedTahun = $request->query('tahun');
        $tahun = is_string($requestedTahun) && preg_match('/^\d{4}$/', $requestedTahun)
            ? (int) $requestedTahun
            : $tahunSekarang;

        if ($tahunList->isNotEmpty() && !$tahunList->contains($tahun)) {
            $tahun = (int) $tahunList->first();
        }

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
            $val = KegiatanAnggaran::whereHas('kegiatan', fn($query) =>
                $query->where('tahun_anggaran', $tahun)
                    ->whereMonth('tanggal_mulai', $m)
            )->sum('nominal_digunakan');

            $penyerapanPerBulan[] = (float) $val;
        }

        $penyerapanPerTriwulan = [];
        for ($q = 1; $q <= 4; $q++) {
            $bulanMulai = (($q - 1) * 3) + 1;
            $bulanSelesai = $bulanMulai + 2;
            $periodeMulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth()->toDateString();
            $periodeSelesai = Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth()->toDateString();

            $val = KegiatanAnggaran::whereHas('kegiatan', fn($query) =>
                $query->where('tahun_anggaran', $tahun)
                    ->whereBetween('tanggal_mulai', [$periodeMulai, $periodeSelesai])
            )->sum('nominal_digunakan');

            $penyerapanPerTriwulan[] = (float) $val;
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

        // ── KEGIATAN TERBARU ──────────────────────────────────────────────
        $kegiatanTerbaru = Kegiatan::with(['sasaran', 'pagu', 'anggarans', 'createdBy.subBagian'])
            ->where('tahun_anggaran', $tahun)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();


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
            'user', 'tahun', 'tahunList', 'today',
            'paguTahunIni', 'realisasiTahunIni', 'sisaAnggaran', 'pctPenyerapan',
            'penyerapanPerBulan', 'penyerapanPerTriwulan', 'penyerapanPerPagu',
            'totalKegiatan', 'kegiatanBulanIni',
            'kegiatanTerbaru',
            'totalSasaran',
            'penyerapanPerSubBagian', 'maxRealisasiSubBagian'
        ));
    }
}

