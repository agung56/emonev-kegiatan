<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\KegiatanAnggaran;
use App\Models\Pagu;
use App\Models\PaguKomponen;
use App\Models\Sasaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
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

        $tahunPaguList = Pagu::query()
            ->distinct()
            ->pluck('tahun_anggaran')
            ->filter(fn ($tahun) => filled($tahun))
            ->map(fn ($tahun) => (int) $tahun)
            ->unique()
            ->sortDesc()
            ->values();

        $requestedTahun = $request->query('tahun');
        if (is_string($requestedTahun) && preg_match('/^\d{4}$/', $requestedTahun)) {
            $tahun = (int) $requestedTahun;
        } elseif ($tahunPaguList->isNotEmpty()) {
            $tahun = (int) $tahunPaguList->first();
        } elseif ($tahunList->isNotEmpty()) {
            $tahun = (int) $tahunList->first();
        } else {
            $tahun = $tahunSekarang;
        }

        if ($tahunList->isNotEmpty() && !$tahunList->contains($tahun)) {
            $tahun = (int) $tahunList->first();
        }

        if ($tahunPaguList->contains($tahun)) {
            $tahunPaguReferensi = $tahun;
        } elseif ($tahunPaguList->isNotEmpty()) {
            $tahunPaguReferensi = (int) $tahunPaguList->first();
        } else {
            $tahunPaguReferensi = null;
        }

        $bulanIni = date('n');
        $today = Carbon::today();

        $kegiatanTahunIni = Kegiatan::with(['pagu', 'anggarans.paguDetail'])
            ->where('tahun_anggaran', $tahun)
            ->get();

        $anggaranKegiatanTahunIni = $kegiatanTahunIni->flatMap(
            fn (Kegiatan $kegiatan) => $kegiatan->anggarans
        );

        $masterPaguIds = $tahunPaguReferensi !== null
            ? Pagu::where('tahun_anggaran', $tahunPaguReferensi)->pluck('id')
            : collect();

        $relatedPaguIds = collect()
            ->merge($masterPaguIds)
            ->merge($kegiatanTahunIni->pluck('pagu_id'))
            ->merge($anggaranKegiatanTahunIni->map(fn ($anggaran) => optional($anggaran->paguDetail)->pagu_id))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $masterKomponenTahunIni = $relatedPaguIds->isNotEmpty()
            ? PaguKomponen::with(['pagu', 'details'])
                ->whereIn('pagu_id', $relatedPaguIds)
                ->get()
            : collect();

        $paguMasterTahunIni = $masterPaguIds->isNotEmpty()
            ? (float) Pagu::whereIn('id', $masterPaguIds)->sum('total_nominal')
            : 0.0;

        $paguDariKegiatan = (float) $anggaranKegiatanTahunIni
            ->filter(fn ($anggaran) => filled($anggaran->pagu_detail_id))
            ->unique('pagu_detail_id')
            ->sum(fn ($anggaran) => (float) optional($anggaran->paguDetail)->nominal);

        $paguTahunIni = $paguMasterTahunIni > 0 ? $paguMasterTahunIni : $paguDariKegiatan;

        $realisasiTahunIni = (float) $anggaranKegiatanTahunIni->sum('nominal_digunakan');
        $sisaAnggaran = $paguTahunIni - $realisasiTahunIni;
        $pctPenyerapan = $paguTahunIni > 0
            ? round(($realisasiTahunIni / $paguTahunIni) * 100, 2)
            : 0;

        $penyerapanPerBulan = [];
        for ($m = 1; $m <= 12; $m++) {
            $val = KegiatanAnggaran::whereHas('kegiatan', fn ($query) => $query
                ->where('tahun_anggaran', $tahun)
                ->whereMonth('tanggal_mulai', $m))
                ->sum('nominal_digunakan');

            $penyerapanPerBulan[] = (float) $val;
        }

        $penyerapanPerTriwulan = [];
        for ($q = 1; $q <= 4; $q++) {
            $bulanMulai = (($q - 1) * 3) + 1;
            $bulanSelesai = $bulanMulai + 2;
            $periodeMulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth()->toDateString();
            $periodeSelesai = Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth()->toDateString();

            $val = KegiatanAnggaran::whereHas('kegiatan', fn ($query) => $query
                ->where('tahun_anggaran', $tahun)
                ->whereBetween('tanggal_mulai', [$periodeMulai, $periodeSelesai]))
                ->sum('nominal_digunakan');

            $penyerapanPerTriwulan[] = (float) $val;
        }

        $realisasiPerKomponen = $anggaranKegiatanTahunIni
            ->filter(fn ($anggaran) => filled(optional($anggaran->paguDetail)->pagu_komponen_id))
            ->groupBy(fn ($anggaran) => (int) optional($anggaran->paguDetail)->pagu_komponen_id)
            ->map(fn ($items) => (float) $items->sum('nominal_digunakan'));

        $penyerapanPerKegiatan = $masterKomponenTahunIni
            ->map(function (PaguKomponen $komponen) use ($realisasiPerKomponen) {
                $paguKegiatan = (float) $komponen->details->sum('nominal');
                $realisasi = (float) ($realisasiPerKomponen->get((int) $komponen->id) ?? 0);
                $sisa = $paguKegiatan - $realisasi;
                $pct = $paguKegiatan > 0
                    ? round(($realisasi / $paguKegiatan) * 100, 1)
                    : 0;

                return [
                    'kegiatan' => $komponen->nama_kegiatan_label,
                    'program' => $komponen->pagu?->program_label,
                    'pagu' => $paguKegiatan,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'pct' => $pct,
                ];
            })
            ->sortByDesc('pagu')
            ->values();

        $totalKegiatan = Kegiatan::where('tahun_anggaran', $tahun)->count();
        $kegiatanBulanIni = Kegiatan::where('tahun_anggaran', $tahun)
            ->whereMonth('tanggal_mulai', $bulanIni)
            ->count();

        $kegiatanTerbaru = Kegiatan::with(['sasaran', 'pagu', 'anggarans', 'createdBy.subBagian', 'subBagianPelaksana'])
            ->where('tahun_anggaran', $tahun)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalSasaran = Sasaran::where('tahun_anggaran', $tahun)
            ->where('is_aktif', true)
            ->count();

        $penyerapanPerSubBagian = DB::table('sub_bagians as sb')
            ->leftJoin('kegiatans as k', function ($join) use ($tahun) {
                $join->on('k.sub_bagian_id', '=', 'sb.id')
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
            'user',
            'tahun',
            'tahunPaguReferensi',
            'tahunList',
            'today',
            'paguTahunIni',
            'realisasiTahunIni',
            'sisaAnggaran',
            'pctPenyerapan',
            'penyerapanPerBulan',
            'penyerapanPerTriwulan',
            'penyerapanPerKegiatan',
            'totalKegiatan',
            'kegiatanBulanIni',
            'kegiatanTerbaru',
            'totalSasaran',
            'penyerapanPerSubBagian',
            'maxRealisasiSubBagian'
        ));
    }
}
