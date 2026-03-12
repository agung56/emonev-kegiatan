@extends('layouts.app')
@section('page_title', 'Rekap Kegiatan')

@section('content')

@php
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

<div class="p-6 space-y-6">

    {{-- Flash --}}
    @if(session('success'))
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
         class="flex items-center gap-3 px-5 py-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 text-green-700 dark:text-green-400 rounded-2xl text-sm font-bold">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Rekap Kegiatan</h1>
            <p class="text-sm text-slate-500 font-medium">Realisasi anggaran dan capaian indikator kegiatan</p>
        </div>
        {{-- <button onclick="window.print()"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-slate-800 dark:bg-white text-white dark:text-slate-900 text-xs font-black rounded-2xl shadow-lg hover:opacity-90 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            CETAK LAPORAN
        </button> --}}
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('rekap.index') }}" id="filterForm"
          class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 p-5 shadow-sm"
          x-data="{
              periode: '{{ $periode }}',
              get showBulan()    { return this.periode === 'bulan' },
              get showTriwulan() { return this.periode === 'triwulan' },
          }">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">

            {{-- Tahun --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun</label>
                <input type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2099"
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
            </div>

            {{-- Periode --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Periode</label>
                <div class="relative">
                    <select name="periode" x-model="periode"
                            class="appearance-none w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-9">
                        <option value="bulan" {{ $periode==='bulan'?'selected':'' }}>Bulanan</option>
                        <option value="triwulan" {{ $periode==='triwulan'?'selected':'' }}>Triwulanan</option>
                        <option value="tahunan" {{ $periode==='tahunan'?'selected':'' }}>Tahunan</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                </div>
            </div>

            {{-- Bulan --}}
            <div class="space-y-1.5" x-show="showBulan" x-transition>
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Bulan</label>
                <div class="relative">
                    <select name="bulan"
                            class="appearance-none w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-9">
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $bulan==$m?'selected':'' }}>{{ $namaBulan[$m] }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                </div>
            </div>

            {{-- Triwulan --}}
            <div class="space-y-1.5" x-show="showTriwulan" x-transition>
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Triwulan</label>
                <div class="relative">
                    <select name="triwulan"
                            class="appearance-none w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-9">
                        <option value="1" {{ $triwulan==1?'selected':'' }}>Triwulan I (Jan–Mar)</option>
                        <option value="2" {{ $triwulan==2?'selected':'' }}>Triwulan II (Apr–Jun)</option>
                        <option value="3" {{ $triwulan==3?'selected':'' }}>Triwulan III (Jul–Sep)</option>
                        <option value="4" {{ $triwulan==4?'selected':'' }}>Triwulan IV (Okt–Des)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                </div>
            </div>

            @if(false)
            {{-- Semester --}}
            <div class="hidden">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Semester</label>
                <div class="relative">
                    <select disabled
                            class="appearance-none w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-9">
                        <option value="1" {{ $semester==1?'selected':'' }}>Semester I (Jan–Jun)</option>
                        <option value="2" {{ $semester==2?'selected':'' }}>Semester II (Jul–Des)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                </div>
            </div>

            @endif
            {{-- Tombol --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-black text-transparent uppercase tracking-widest ml-1 select-none">_</label>
                <button type="submit"
                        class="w-full px-4 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest cursor-pointer shadow-lg shadow-brand-primary/20">
                    Tampilkan
                </button>
            </div>

        </div>
    </form>

    {{-- ── KONTEN REKAP ── --}}
    <div id="rekapContent" class="space-y-6">

        @if($sasarans->isEmpty() && empty($rekapAnggaran))
        {{-- Empty state --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex flex-col items-center gap-3 py-20">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Tidak ada data untuk periode ini</p>
                <p class="text-xs text-slate-400">Belum ada kegiatan yang dicatat pada periode <span class="font-bold text-brand-primary">{{ $labelPeriode }}</span></p>
            </div>
        </div>
        @else

        {{-- Loop per Sasaran → per Indikator --}}
        @foreach($sasarans as $sasaran)
        @php
            $allKegiatanInSasaran = $sasaran->indikators->flatMap(fn($i) => $i->kegiatans)->unique('id');
            if ($allKegiatanInSasaran->isEmpty()) continue;
        @endphp

        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">

            {{-- Header Sasaran --}}
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-white/5 border-b border-slate-100 dark:border-white/5 flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sasaran · {{ ucfirst($sasaran->kepemilikan) }}</p>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white mt-0.5">{{ $sasaran->nama_sasaran }}</h3>
                    </div>
                </div>
                <span class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary rounded-full text-[10px] font-black uppercase tracking-widest shrink-0">
                    {{ $labelPeriode }}
                </span>
            </div>

            {{-- Loop per Indikator --}}
            @foreach($sasaran->indikators as $indikator)
            @php
                $kegiatans = $indikator->kegiatans;
                if ($kegiatans->isEmpty()) continue;

                // Hitung ringkasan anggaran per akun untuk indikator ini
                $anggaranPerAkun = collect();
                foreach ($kegiatans as $kg) {
                    foreach ($kg->anggarans as $ang) {
                        $key = $ang->paguDetail->nama_akun ?? 'Lainnya';
                        $paguNominal = $ang->paguDetail->nominal ?? 0;
                        if ($anggaranPerAkun->has($key)) {
                            $anggaranPerAkun[$key]['realisasi'] += $ang->nominal_digunakan;
                        } else {
                            $anggaranPerAkun[$key] = [
                                'nama_akun'  => $key,
                                'pagu'       => $paguNominal,
                                'realisasi'  => $ang->nominal_digunakan,
                            ];
                        }
                    }
                }
                $anggaranPerAkun = $anggaranPerAkun->values();
            @endphp

            <div class="border-b border-slate-100 dark:border-white/5 last:border-0">

                {{-- Sub Header Indikator --}}
                <div class="px-6 py-3 bg-blue-50/40 dark:bg-blue-500/5 flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-brand-primary shrink-0"></div>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $indikator->nama_indikator }}</p>
                    @php
                        $totalPaguInd      = $anggaranPerAkun->sum('pagu');
                        $totalRealisasiInd = $anggaranPerAkun->sum('realisasi');
                        $sisaInd           = $totalPaguInd - $totalRealisasiInd;
                        $pctInd            = $totalPaguInd > 0 ? round(($totalRealisasiInd / $totalPaguInd) * 100, 2) : 0;
                    @endphp
                    <div class="ml-auto flex items-center gap-4 flex-wrap justify-end">
                        <span class="text-[11px] text-slate-400 font-medium">Pagu: <span class="font-black text-slate-600 dark:text-slate-300">Rp {{ number_format($totalPaguInd,0,',','.') }}</span></span>
                        <span class="text-[11px] text-slate-400 font-medium">Realisasi: <span class="font-black text-brand-primary">Rp {{ number_format($totalRealisasiInd,0,',','.') }}</span></span>
                        <span class="text-[11px] text-slate-400 font-medium">Sisa: <span class="font-black text-slate-600 dark:text-slate-300">Rp {{ number_format($sisaInd,0,',','.') }}</span></span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $pctInd >= 80 ? 'bg-green-100 text-green-600' : ($pctInd >= 40 ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-500') }}">
                            {{ $pctInd }}%
                        </span>
                    </div>
                </div>

                {{-- Tabel Ringkasan Anggaran per Akun --}}
                @if($anggaranPerAkun->isNotEmpty())
                <div class="overflow-x-auto border-b border-slate-100 dark:border-white/5">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-white/[0.02]">
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Ringkasan Anggaran (Budget Allocation)</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Pagu</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Realisasi</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Sisa</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                            @foreach($anggaranPerAkun as $akun)
                            @php
                                $sisa = $akun['pagu'] - $akun['realisasi'];
                                $pct  = $akun['pagu'] > 0 ? round(($akun['realisasi'] / $akun['pagu']) * 100, 2) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $akun['nama_akun'] }}</td>
                                <td class="px-6 py-3 text-sm text-slate-500 text-right">Rp {{ number_format($akun['pagu'],0,',','.') }}</td>
                                <td class="px-6 py-3 text-sm font-bold text-brand-primary text-right">Rp {{ number_format($akun['realisasi'],0,',','.') }}</td>
                                <td class="px-6 py-3 text-sm text-slate-500 text-right">Rp {{ number_format($sisa,0,',','.') }}</td>
                                <td class="px-6 py-3 text-right">
                                    <span class="text-sm font-black {{ $pct >= 80 ? 'text-green-500' : ($pct >= 40 ? 'text-amber-500' : 'text-red-500') }}">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Tabel Kegiatan --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-white/[0.02]">
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Kegiatan</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Pagu</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Lokus</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Akun Anggaran</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanggal</th>
                                <th class="px-6 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                            @foreach($kegiatans as $kg)
                            @php $totalKg = $kg->anggarans->sum('nominal_digunakan'); @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-3">
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200 leading-snug">{{ $kg->nama_kegiatan }}</p>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider mt-1 inline-block {{ $kg->kepemilikan === 'lembaga' ? 'bg-purple-100 text-purple-600' : 'bg-orange-100 text-orange-600' }}">{{ $kg->kepemilikan }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-snug">{{ $kg->pagu?->kegiatan ?? '—' }}</p>
                                    <p class="text-[10px] text-brand-primary font-black mt-0.5">TA {{ $kg->pagu?->tahun_anggaran ?? '' }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-500 font-medium">{{ $kg->lokus ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($kg->anggarans as $ang)
                                        <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 rounded text-[10px] font-bold">{{ $ang->paguDetail->nama_akun ?? '-' }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-400 font-medium whitespace-nowrap">
                                    {{ $kg->tanggal_mulai->format('d M') }} – {{ $kg->tanggal_selesai->format('d M Y') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="text-sm font-black text-slate-700 dark:text-white">Rp {{ number_format($totalKg,0,',','.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>{{-- end indikator --}}
            @endforeach

        </div>{{-- end sasaran --}}
        @endforeach

        @endif
    </div>{{-- end rekapContent --}}

</div>

{{-- ══════════════════════════════════════════
     PRINT AREA — hanya muncul saat cetak
══════════════════════════════════════════ --}}
<div id="printArea" class="hidden">
    <div class="print-page">

        {{-- KOP SURAT --}}
        <div class="kop">
            <img src="{{ asset('assets/logo-kpu.png') }}" class="kop-logo" alt="Logo KPU">
            <div class="kop-text">
                <div class="kop-title">KOMISI PEMILIHAN UMUM</div>
                <div class="kop-title">KABUPATEN PASURUAN</div>
                <div class="kop-sub">Alamat: JL. SUDARSONO NO. 1 POGAR BANGIL – PASURUAN</div>
                <div class="kop-sub">Telp: (0343) 747142, 747143 &nbsp; Email: kab_pasuruan@kpu.go.id</div>
            </div>
        </div>
        <div class="kop-line-double">
            <div class="line-thick"></div>
            <div class="line-thin"></div>
        </div>

        {{-- Judul Laporan --}}
        <div class="print-judul">
            <div class="print-judul-title">LAPORAN REALISASI KEGIATAN</div>
            <div class="print-judul-sub">Periode: {{ strtoupper($labelPeriode) }}</div>
        </div>

        {{-- Loop sasaran & indikator untuk print --}}
        @foreach($sasarans as $sasaran)
        @php
            $allKegiatanInSasaran = $sasaran->indikators->flatMap(fn($i) => $i->kegiatans)->unique('id');
            if ($allKegiatanInSasaran->isEmpty()) continue;
        @endphp

        <div class="print-sasaran">
            <div class="print-sasaran-header">
                <span class="print-sasaran-label">SASARAN</span>
                <span class="print-sasaran-name">{{ $sasaran->nama_sasaran }}</span>
            </div>

            @foreach($sasaran->indikators as $indikator)
            @php
                $kegiatans = $indikator->kegiatans;
                if ($kegiatans->isEmpty()) continue;

                $anggaranPerAkun = collect();
                foreach ($kegiatans as $kg) {
                    foreach ($kg->anggarans as $ang) {
                        $key = $ang->paguDetail->nama_akun ?? 'Lainnya';
                        $paguNominal = $ang->paguDetail->nominal ?? 0;
                        if ($anggaranPerAkun->has($key)) {
                            $anggaranPerAkun[$key]['realisasi'] += $ang->nominal_digunakan;
                        } else {
                            $anggaranPerAkun[$key] = ['nama_akun' => $key, 'pagu' => $paguNominal, 'realisasi' => $ang->nominal_digunakan];
                        }
                    }
                }
                $anggaranPerAkun = $anggaranPerAkun->values();
                $totalPaguInd      = $anggaranPerAkun->sum('pagu');
                $totalRealisasiInd = $anggaranPerAkun->sum('realisasi');
                $sisaInd           = $totalPaguInd - $totalRealisasiInd;
                $pctInd            = $totalPaguInd > 0 ? round(($totalRealisasiInd / $totalPaguInd) * 100, 2) : 0;
            @endphp

            <div class="print-indikator">
                <div class="print-indikator-header">
                    <span class="print-indikator-bullet">▸</span>
                    <span class="print-indikator-name">{{ $indikator->nama_indikator }}</span>
                    <span class="print-indikator-pct {{ $pctInd >= 80 ? 'pct-green' : ($pctInd >= 40 ? 'pct-amber' : 'pct-red') }}">{{ $pctInd }}%</span>
                </div>

                {{-- Ringkasan per Akun --}}
                @if($anggaranPerAkun->isNotEmpty())
                <table class="print-table">
                    <thead>
                        <tr>
                            <th class="text-left">Ringkasan Anggaran</th>
                            <th class="text-right">Pagu</th>
                            <th class="text-right">Realisasi</th>
                            <th class="text-right">Sisa</th>
                            <th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anggaranPerAkun as $akun)
                        @php
                            $sisa = $akun['pagu'] - $akun['realisasi'];
                            $pct  = $akun['pagu'] > 0 ? round(($akun['realisasi'] / $akun['pagu']) * 100, 2) : 0;
                        @endphp
                        <tr>
                            <td>{{ $akun['nama_akun'] }}</td>
                            <td class="text-right">Rp {{ number_format($akun['pagu'],0,',','.') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($akun['realisasi'],0,',','.') }}</td>
                            <td class="text-right">Rp {{ number_format($sisa,0,',','.') }}</td>
                            <td class="text-right {{ $pct >= 80 ? 'pct-green' : ($pct >= 40 ? 'pct-amber' : 'pct-red') }}">{{ $pct }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                {{-- Daftar Kegiatan --}}
                <table class="print-table" style="margin-top:6px">
                    <thead>
                        <tr>
                            <th class="text-left" style="width:22%">Kegiatan</th>
                            <th class="text-left" style="width:18%">Nama Pagu</th>
                            <th class="text-left" style="width:12%">Lokus</th>
                            <th class="text-left" style="width:18%">Akun Anggaran</th>
                            <th class="text-left" style="width:15%">Tanggal</th>
                            <th class="text-right" style="width:15%">Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kegiatans as $kg)
                        @php $totalKg = $kg->anggarans->sum('nominal_digunakan'); @endphp
                        <tr>
                            <td>{{ $kg->nama_kegiatan }}</td>
                            <td>{{ $kg->pagu?->kegiatan ?? '—' }} <span style="color:#666;font-size:8pt">(TA {{ $kg->pagu?->tahun_anggaran ?? '' }})</span></td>
                            <td>{{ $kg->lokus ?? '—' }}</td>
                            <td>{{ $kg->anggarans->map(fn($a) => $a->paguDetail->nama_akun ?? '-')->join(', ') }}</td>
                            <td>{{ $kg->tanggal_mulai->format('d M') }} – {{ $kg->tanggal_selesai->format('d M Y') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($totalKg,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>{{-- end print indikator --}}
            @endforeach
        </div>{{-- end print sasaran --}}
        @endforeach

        {{-- TTD --}}
        <div class="print-ttd">
            <div class="ttd-left">
                <div class="ttd-kota">Bangil, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="ttd-jabatan">Mengetahui,</div>
                <div class="ttd-jabatan">Ketua KPU Kabupaten Pasuruan</div>
                <div class="ttd-space"></div>
                <div class="ttd-nama">______________________________</div>
            </div>
            <div class="ttd-right">
                <div class="ttd-kota">&nbsp;</div>
                <div class="ttd-jabatan">Dibuat oleh,</div>
                <div class="ttd-jabatan">Kasubag {{ auth()->user()->subBagian->nama_sub_bagian ?? 'Perencanaan' }}</div>
                <div class="ttd-space"></div>
                <div class="ttd-nama">______________________________</div>
            </div>
        </div>

    </div>
</div>

{{-- ══ PRINT CSS ══ --}}
<style>
@media screen {
    #printArea { display: none !important; }
}

@media print {
    /* Sembunyikan semua elemen, tampilkan hanya printArea */
    body * {
        visibility: hidden !important;
        overflow: visible !important;
    }
    #printArea {
        visibility: visible !important;
        display: block !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        background: #fff !important;
        z-index: 99999 !important;
    }
    #printArea * {
        visibility: visible !important;
    }

    @page {
        size: A4;
        margin: 15mm 20mm 20mm 20mm;
    }

    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        color: #000;
        background: #fff;
    }

    /* KOP */
    .kop {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-bottom: 8px;
    }
    .kop-logo {
        height: 72px;
        width: auto;
        flex-shrink: 0;
    }
    .kop-text {
        flex: 1;
        text-align: center;
    }
    .kop-title {
        font-size: 13pt;
        font-weight: 700;
        text-transform: uppercase;
        line-height: 1.4;
    }
    .kop-sub {
        font-size: 9.5pt;
        margin-top: 2px;
    }
    .kop-line-double {
        margin: 4px 0 12px 0;
    }
    .line-thick {
        height: 3px;
        background: #000;
    }
    .line-thin {
        height: 1px;
        background: #000;
        margin-top: 2px;
    }

    /* Judul */
    .print-judul {
        text-align: center;
        margin: 16px 0 20px 0;
    }
    .print-judul-title {
        font-size: 13pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .print-judul-sub {
        font-size: 11pt;
        margin-top: 4px;
    }

    /* Sasaran */
    .print-sasaran {
        margin-bottom: 18px;
        page-break-inside: avoid;
    }
    .print-sasaran-header {
        background: #e8e8e8;
        padding: 5px 10px;
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 6px;
        border-left: 4px solid #333;
    }
    .print-sasaran-label {
        font-size: 8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
        flex-shrink: 0;
    }
    .print-sasaran-name {
        font-size: 10.5pt;
        font-weight: 700;
    }

    /* Indikator */
    .print-indikator {
        margin-bottom: 14px;
        padding-left: 8px;
        page-break-inside: avoid;
    }
    .print-indikator-header {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 5px;
        padding: 4px 8px;
        background: #f5f5f5;
    }
    .print-indikator-bullet {
        font-size: 9pt;
        color: #333;
        flex-shrink: 0;
    }
    .print-indikator-name {
        font-size: 10pt;
        font-weight: 600;
        flex: 1;
    }
    .print-indikator-pct {
        font-size: 10pt;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Tabel */
    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        margin-bottom: 4px;
    }
    .print-table thead tr {
        background: #ddd;
    }
    .print-table th {
        padding: 4px 8px;
        font-weight: 700;
        font-size: 8.5pt;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #bbb;
    }
    .print-table td {
        padding: 4px 8px;
        border: 1px solid #ccc;
        vertical-align: top;
    }
    .print-table tbody tr:nth-child(even) td {
        background: #fafafa;
    }
    .text-right { text-align: right !important; }
    .text-left  { text-align: left !important; }
    .font-bold  { font-weight: 700; }
    .pct-green  { color: #166534; }
    .pct-amber  { color: #92400e; }
    .pct-red    { color: #991b1b; }

    /* TTD */
    .print-ttd {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        page-break-inside: avoid;
    }
    .ttd-left, .ttd-right {
        width: 45%;
        text-align: center;
    }
    .ttd-kota    { font-size: 10.5pt; margin-bottom: 4px; }
    .ttd-jabatan { font-size: 10.5pt; }
    .ttd-space   { height: 52px; }
    .ttd-nama    { font-size: 10.5pt; font-weight: 700; }
}
</style>

@endsection
