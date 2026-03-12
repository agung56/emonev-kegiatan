<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Sasaran;
use App\Models\Indikator;
use App\Models\PaguDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $tahun    = $request->get('tahun', date('Y'));
        $periode  = $request->get('periode', 'bulan');   // bulan | triwulan | semester | tahunan
        $bulan    = $request->get('bulan', date('n'));
        $triwulan = $request->get('triwulan', 1);        // 1-4
        $semester = $request->get('semester', 1);        // 1-2

        // Tentukan rentang bulan berdasarkan periode
        [$bulanMulai, $bulanAkhir] = $this->getRentangBulan($periode, $bulan, $triwulan, $semester);

        // Label periode untuk tampilan
        $labelPeriode = $this->getLabelPeriode($periode, $bulan, $triwulan, $semester, $tahun);

        // Ambil semua sasaran aktif beserta indikatornya
        $sasarans = Sasaran::with([
            'indikators.kegiatans' => function ($q) use ($tahun, $bulanMulai, $bulanAkhir) {
                $q->where('tahun_anggaran', $tahun)
                  ->whereMonth('tanggal_mulai', '>=', $bulanMulai)
                  ->whereMonth('tanggal_mulai', '<=', $bulanAkhir)
                  ->with(['anggarans.paguDetail', 'pagu']);
            },
        ])
        ->where('tahun_anggaran', $tahun)
        ->where('is_aktif', true)
        ->get();

        // Ambil semua pagu detail yang digunakan di periode ini (untuk ringkasan anggaran)
        $rekapAnggaran = $this->getRekapAnggaran($tahun, $bulanMulai, $bulanAkhir);

        // Tahun-tahun yang tersedia (untuk filter)
        $tahunList = Kegiatan::select('tahun_anggaran')->distinct()->orderBy('tahun_anggaran', 'desc')->pluck('tahun_anggaran');
        if ($tahunList->isEmpty()) {
            $tahunList = collect([date('Y')]);
        }

        return view('admin.rekap.index', compact(
            'sasarans', 'rekapAnggaran', 'tahunList',
            'tahun', 'periode', 'bulan', 'triwulan', 'semester',
            'bulanMulai', 'bulanAkhir', 'labelPeriode'
        ));
    }

    private function getRentangBulan(string $periode, $bulan, $triwulan, $semester): array
    {
        return match ($periode) {
            'bulan'     => [(int)$bulan, (int)$bulan],
            'triwulan'  => [((int)$triwulan - 1) * 3 + 1, (int)$triwulan * 3],
            'semester'  => [(int)$semester === 1 ? 1 : 7, (int)$semester === 1 ? 6 : 12],
            'tahunan'   => [1, 12],
            default     => [(int)$bulan, (int)$bulan],
        };
    }

    private function getLabelPeriode(string $periode, $bulan, $triwulan, $semester, $tahun): string
    {
        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return match ($periode) {
            'bulan'    => $namaBulan[(int)$bulan] . ' ' . $tahun,
            'triwulan' => 'Triwulan ' . ['I','II','III','IV'][$triwulan - 1] . ' ' . $tahun,
            'semester' => 'Semester ' . ((int)$semester === 1 ? 'I' : 'II') . ' ' . $tahun,
            'tahunan'  => 'Tahun ' . $tahun,
            default    => $tahun,
        };
    }

    private function getRekapAnggaran(string $tahun, int $bulanMulai, int $bulanAkhir): array
    {
        // Pagu per akun: ambil semua pagu_detail yang terhubung ke kegiatan di periode ini
        $rows = DB::table('kegiatan_anggarans as ka')
            ->join('kegiatans as k', 'k.id', '=', 'ka.kegiatan_id')
            ->join('pagu_details as pd', 'pd.id', '=', 'ka.pagu_detail_id')
            ->where('k.tahun_anggaran', $tahun)
            ->whereBetween(DB::raw('MONTH(k.tanggal_mulai)'), [$bulanMulai, $bulanAkhir])
            ->select(
                'pd.id as pagu_detail_id',
                'pd.nama_akun',
                'pd.nominal as pagu_nominal',
                DB::raw('SUM(ka.nominal_digunakan) as realisasi')
            )
            ->groupBy('pd.id', 'pd.nama_akun', 'pd.nominal')
            ->get();

        return $rows->map(function ($r) {
            $sisa        = $r->pagu_nominal - $r->realisasi;
            $persentase  = $r->pagu_nominal > 0 ? round(($r->realisasi / $r->pagu_nominal) * 100, 2) : 0;
            return [
                'nama_akun'    => $r->nama_akun,
                'pagu'         => $r->pagu_nominal,
                'realisasi'    => $r->realisasi,
                'sisa'         => $sisa,
                'persentase'   => $persentase,
            ];
        })->toArray();
    }
}