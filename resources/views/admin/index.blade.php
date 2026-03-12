@extends('layouts.app')
@section('page_title', 'Dashboard')

@section('content')
@php
$namaBulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$namaTriwulan = ['TW I', 'TW II', 'TW III', 'TW IV'];
$tahunSekarang = (int) date('Y');
$bulanSekarang = (int) $tahun === $tahunSekarang ? (int) date('n') : 0;
$triwulanSekarang = (int) $tahun === $tahunSekarang ? (int) ceil(date('n') / 3) : 0;
@endphp

<div class="p-6 space-y-6">

    {{-- ══ HERO SELAMAT DATANG ══ --}}
    <div class="relative bg-white dark:bg-brand-black rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden p-7">
        {{-- bg decoration --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(249,115,22,0.08),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(15,23,42,0.04),transparent_35%)] dark:hidden"></div>
            <div class="absolute inset-0 hidden dark:block">
                <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-brand-primary/10"></div>
                <div class="absolute right-32 -bottom-8 w-40 h-40 rounded-full bg-brand-primary/5"></div>
                <div class="absolute top-1/2 right-64 w-2 h-2 rounded-full bg-brand-primary/30"></div>
                <div class="absolute top-1/4 right-48 w-1 h-1 rounded-full bg-brand-primary/50"></div>
            </div>
        </div>

        <div class="relative z-10 space-y-1.5">
            <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.25em]">E-MONEV · KPU KAB. PASURUAN</p>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white leading-tight">
                Selamat Datang, <br class="md:hidden"><span class="text-brand-primary">{{ auth()->user()->name }}</span>! 👋
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                {{ $today->translatedFormat('l, d F Y') }} &nbsp;·&nbsp; Tahun Anggaran <span class="text-slate-900 dark:text-white font-black">{{ $tahun }}</span>
            </p>
        </div>

    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter Dashboard</p>
            <p class="text-sm text-slate-500 font-medium mt-1">Tampilkan ringkasan dashboard berdasarkan tahun anggaran.</p>
        </div>
        <form action="{{ route('dashboard') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative min-w-[180px]">
                <select name="tahun" onchange="this.form.submit()"
                        class="appearance-none w-full px-5 py-3.5 pr-10 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all">
                    @forelse($tahunList as $year)
                    <option value="{{ $year }}" {{ (int) $tahun === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @empty
                    <option value="{{ $tahun }}" selected>{{ $tahun }}</option>
                    @endforelse
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            @if(request()->has('tahun'))
            <a href="{{ route('dashboard') }}"
               class="px-4 py-3 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-2 border-slate-200 dark:border-white/10 rounded-2xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ══ STAT CARDS ══ --}}
    <div class="grid grid-cols-1 gap-4">

        {{-- Total Kegiatan --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 p-5 hover:border-brand-primary/40 transition-all group">
            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-2xl bg-brand-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">TA {{ $tahun }}</span>
            </div>
            <div class="mt-3">
                <p class="text-3xl font-black text-slate-800 dark:text-white group-hover:text-brand-primary transition-colors">{{ $totalKegiatan }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Kegiatan</p>
            </div>
        </div>

    </div>

    {{-- ══ BARIS TENGAH: Penyerapan Per Pagu + Chart Bulanan ══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Penyerapan per Pagu (2/3) --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Penyerapan Anggaran</h2>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Per sumber pagu · TA {{ $tahun }}</p>
                </div>
                <span class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary rounded-full text-[10px] font-black">
                    {{ number_format($pctPenyerapan, 1) }}% Total
                </span>
            </div>

            {{-- Besar: total penyerapan bar --}}
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 dark:border-white/5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Total Realisasi vs Pagu</span>
                    <span class="text-xs font-black text-brand-primary">Rp {{ number_format($realisasiTahunIni, 0, ',', '.') }}</span>
                </div>
                <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-primary rounded-full transition-all duration-1000"
                         style="width: {{ min($pctPenyerapan, 100) }}%"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-[10px] text-slate-400 font-medium">0</span>
                    <span class="text-[10px] text-slate-400 font-medium">Pagu: Rp {{ number_format($paguTahunIni, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Per pagu --}}
            <div class="divide-y divide-slate-50 dark:divide-white/5">
                @forelse($penyerapanPerPagu as $pg)
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between gap-4 mb-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate">{{ $pg['kegiatan'] }}</p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-[10px] text-slate-400 font-medium">Pagu: Rp {{ number_format($pg['pagu'], 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400">·</span>
                                <span class="text-[10px] text-slate-400 font-medium">Sisa: Rp {{ number_format($pg['sisa'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-sm font-black {{ $pg['pct'] >= 80 ? 'text-green-500' : ($pg['pct'] >= 40 ? 'text-amber-500' : 'text-red-500') }}">
                                {{ $pg['pct'] }}%
                            </span>
                            <p class="text-[10px] text-brand-primary font-bold">Rp {{ number_format($pg['realisasi'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700
                            {{ $pg['pct'] >= 80 ? 'bg-green-400' : ($pg['pct'] >= 40 ? 'bg-amber-400' : 'bg-brand-primary') }}"
                             style="width: {{ min($pg['pct'], 100) }}%"></div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center text-sm text-slate-400 italic">Belum ada data pagu.</div>
                @endforelse
            </div>
        </div>

        {{-- Penyerapan pagu global --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-brand-primary"></div>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Pagu Anggaran Tahun {{ $tahun }}</span>
                    </div>
                    <span class="text-xs font-black text-brand-primary">{{ $pctPenyerapan }}%</span>
                </div>

                {{-- Donut mini via SVG --}}
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16 shrink-0">
                        <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="13" fill="none" stroke="currentColor" stroke-width="4" class="text-slate-100 dark:text-slate-800"/>
                            <circle cx="18" cy="18" r="13" fill="none"
                                stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                stroke-dasharray="{{ $pctPenyerapan }}, 100"
                                class="text-brand-primary"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-[10px] font-black text-slate-700 dark:text-white">{{ $pctPenyerapan }}%</span>
                        </div>
                    </div>
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Realisasi</p>
                            <p class="text-sm font-black text-brand-primary truncate">Rp {{ number_format($realisasiTahunIni/1000000, 1, ',', '.') }}Jt</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pagu</p>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 truncate">Rp {{ number_format($paguTahunIni/1000000, 1, ',', '.') }}Jt</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sisa</p>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 truncate">Rp {{ number_format($sisaAnggaran/1000000, 1, ',', '.') }}Jt</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sasaran aktif --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sasaran Aktif</p>
                    <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $totalSasaran }}</p>
                    <p class="text-[10px] text-slate-400 font-medium">TA {{ $tahun }}</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ CHART PENYERAPAN BULANAN ══ --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden"
         x-data="{ trendMode: 'monthly' }">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Tren Realisasi</h2>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5"
                   x-text="trendMode === 'monthly' ? 'Realisasi anggaran per bulan untuk TA {{ $tahun }}' : 'Realisasi anggaran per triwulan untuk TA {{ $tahun }}'"></p>
            </div>
            <div class="inline-flex items-center gap-1 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800">
                <button type="button" @click="trendMode = 'monthly'"
                        :class="trendMode === 'monthly' ? 'bg-white dark:bg-slate-700 text-brand-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    Bulanan
                </button>
                <button type="button" @click="trendMode = 'quarterly'"
                        :class="trendMode === 'quarterly' ? 'bg-white dark:bg-slate-700 text-brand-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    Triwulanan
                </button>
            </div>
        </div>
        <div class="p-6">
            <div x-show="trendMode === 'monthly'" x-cloak>
                <div class="flex items-end gap-2 h-36 trend-bars">
                    @php $maxValBulanan = max(array_merge($penyerapanPerBulan, [1])); @endphp
                    @foreach($penyerapanPerBulan as $i => $val)
                    @php
                        $bulan = $i + 1;
                        $heightPct = $maxValBulanan > 0 ? round(($val / $maxValBulanan) * 100) : 0;
                        $isCurrentMonth = $bulanSekarang > 0 && $bulan === $bulanSekarang;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5 group/bar">
                        <div class="w-full relative flex items-end justify-center" style="height:120px">
                            @if($val > 0)
                            <div data-bar-height="{{ $heightPct }}"
                                 class="relative w-full max-w-[40px] rounded-t-lg transition-all duration-700 {{ $isCurrentMonth ? 'bg-brand-primary' : 'bg-brand-primary/30 group-hover/bar:bg-brand-primary/60' }}"
                                 style="height: {{ $heightPct }}%"
                                 title="{{ $namaBulan[$bulan] }}: Rp {{ number_format($val, 0, ',', '.') }}">
                            </div>
                            @else
                            <div class="w-full max-w-[40px] rounded-t-lg bg-slate-100 dark:bg-slate-800" style="height: 4px"></div>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold {{ $isCurrentMonth ? 'text-brand-primary' : 'text-slate-400' }} uppercase">{{ $namaBulan[$bulan] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-4 mt-4 pt-4 border-t border-slate-100 dark:border-white/5">
                    @if($bulanSekarang > 0)
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded bg-brand-primary"></div>
                        <span class="text-[10px] font-bold text-slate-500">Bulan ini ({{ $namaBulan[$bulanSekarang] }})</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded bg-brand-primary/30"></div>
                        <span class="text-[10px] font-bold text-slate-500">Bulan lainnya</span>
                    </div>
                    @else
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded bg-brand-primary/30"></div>
                        <span class="text-[10px] font-bold text-slate-500">Semua bulan pada TA {{ $tahun }}</span>
                    </div>
                    @endif
                    @php $totalBulanan = array_sum($penyerapanPerBulan); @endphp
                    <div class="ml-auto text-[10px] font-black text-slate-500">
                        Total TA: <span class="text-brand-primary">Rp {{ number_format($totalBulanan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div x-show="trendMode === 'quarterly'" x-cloak>
                <div class="flex items-end gap-2 h-36 trend-bars">
                    @php $maxVal = max(array_merge($penyerapanPerTriwulan, [1])); @endphp
                    @foreach($penyerapanPerTriwulan as $i => $val)
                    @php
                        $triwulan = $i + 1;
                        $heightPct = $maxVal > 0 ? round(($val / $maxVal) * 100) : 0;
                        $isNow = $triwulanSekarang > 0 && $triwulan === $triwulanSekarang;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5 group/bar">
                        <div class="w-full relative flex items-end justify-center" style="height:120px">
                            @if($val > 0)
                            <div data-bar-height="{{ $heightPct }}"
                                 class="relative w-full max-w-[72px] rounded-t-lg transition-all duration-700 {{ $isNow ? 'bg-brand-primary' : 'bg-brand-primary/30 group-hover/bar:bg-brand-primary/60' }}"
                                 style="height: {{ $heightPct }}%"
                                 title="{{ $namaTriwulan[$i] }}: Rp {{ number_format($val, 0, ',', '.') }}">
                            </div>
                            @else
                            <div class="w-full max-w-[72px] rounded-t-lg bg-slate-100 dark:bg-slate-800" style="height: 4px"></div>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold {{ $isNow ? 'text-brand-primary' : 'text-slate-400' }} uppercase">{{ $namaTriwulan[$i] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-4 mt-4 pt-4 border-t border-slate-100 dark:border-white/5">
                    @if($triwulanSekarang > 0)
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded bg-brand-primary"></div>
                        <span class="text-[10px] font-bold text-slate-500">Triwulan berjalan ({{ $namaTriwulan[$triwulanSekarang - 1] }})</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded bg-brand-primary/30"></div>
                        <span class="text-[10px] font-bold text-slate-500">Triwulan lainnya</span>
                    </div>
                    @else
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded bg-brand-primary/30"></div>
                        <span class="text-[10px] font-bold text-slate-500">Semua triwulan pada TA {{ $tahun }}</span>
                    </div>
                    @endif
                    @php $totalChart = array_sum($penyerapanPerTriwulan); @endphp
                    <div class="ml-auto text-[10px] font-black text-slate-500">
                        Total TA: <span class="text-brand-primary">Rp {{ number_format($totalChart, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ══ PENYERAPAN PER SUB BAGIAN ══ --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Penggunaan Anggaran per Sub Bagian</h2>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Berdasarkan kegiatan yang dibuat · TA {{ $tahun }}</p>
            </div>
            <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>

        @if($penyerapanPerSubBagian->isEmpty())
        <div class="flex flex-col items-center gap-2 py-10">
            <p class="text-xs font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Belum ada data</p>
        </div>
        @else
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-5">
            @foreach($penyerapanPerSubBagian as $i => $sb)
            @php
                $barPct = $maxRealisasiSubBagian > 0
                    ? round(($sb->total_realisasi / $maxRealisasiSubBagian) * 100)
                    : 0;
                $colors = ['brand-primary', 'blue-500', 'purple-500', 'amber-500', 'green-500', 'rose-500', 'indigo-500', 'teal-500'];
                $color  = $colors[$i % count($colors)];
            @endphp
            <div class="space-y-1.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-2 h-2 rounded-full bg-{{ $color }} shrink-0"></div>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ $sb->nama_sub_bagian }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-xs font-black text-slate-700 dark:text-white">
                            Rp {{ $sb->total_realisasi >= 1000000
                                ? number_format($sb->total_realisasi / 1000000, 1, ',', '.') . 'Jt'
                                : number_format($sb->total_realisasi, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-{{ $color }} transition-all duration-700"
                             style="width: {{ $barPct }}%"></div>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 w-16 text-right shrink-0">
                        {{ $sb->total_kegiatan }} kegiatan
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Total footer --}}
        <div class="px-6 py-3 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/5 flex items-center justify-between">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Seluruh Sub Bagian</span>
            <span class="text-sm font-black text-brand-primary">
                Rp {{ number_format($penyerapanPerSubBagian->sum('total_realisasi'), 0, ',', '.') }}
            </span>
        </div>
        @endif
    </div>

    {{-- ══ BARIS BAWAH: Kegiatan Terbaru ══ --}}
    <div class="grid grid-cols-1 gap-6">

        {{-- Kegiatan Terbaru --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Kegiatan Terbaru</h2>
                <a href="{{ route('kegiatans.index') }}" class="text-[10px] font-black text-brand-primary uppercase tracking-widest hover:opacity-70 transition-opacity">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-white/5">
                @forelse($kegiatanTerbaru as $kg)
                <a href="{{ route('kegiatans.show', $kg->id) }}"
                   class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group/item">
                    <div class="w-9 h-9 rounded-2xl flex items-center justify-center shrink-0 mt-0.5
                        {{ $kg->kepemilikan === 'lembaga' ? 'bg-purple-100 dark:bg-purple-500/10' : 'bg-orange-100 dark:bg-orange-500/10' }}">
                        <svg class="w-4 h-4 {{ $kg->kepemilikan === 'lembaga' ? 'text-purple-500' : 'text-orange-500' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate group-hover/item:text-brand-primary transition-colors">
                            {{ $kg->nama_kegiatan }}
                        </p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <span class="text-[10px] text-slate-400 font-medium">{{ $kg->pagu?->kegiatan ?? '-' }}</span>
                            @php $totalKg = $kg->anggarans->sum('nominal_digunakan'); @endphp
                            @if($totalKg > 0)
                            <span class="text-[10px] text-brand-primary font-black">Rp {{ number_format($totalKg, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        @if($kg->createdBy?->subBagian)
                        <div class="flex items-center gap-1 mt-1">
                            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $kg->createdBy->subBagian->nama_sub_bagian }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[10px] text-slate-400 font-medium whitespace-nowrap">{{ $kg->created_at->diffForHumans() }}</p>
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase mt-0.5 {{ $kg->kepemilikan === 'lembaga' ? 'bg-purple-100 text-purple-500 dark:bg-purple-500/10' : 'bg-orange-100 text-orange-500 dark:bg-orange-500/10' }}">{{ $kg->kepemilikan }}</span>
                    </div>
                </a>
                @empty
                <div class="flex flex-col items-center gap-2 py-12">
                    <svg class="w-8 h-8 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <p class="text-xs font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Belum ada kegiatan</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

{{-- Carbon locale untuk translatedFormat --}}
@once
@push('scripts')
<script>
    // animasi bar chart on load
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.trend-bars [data-bar-height]');
        bars.forEach((el, i) => {
            const h = `${el.dataset.barHeight}%`;
            el.style.height = '0%';
            setTimeout(() => { el.style.height = h; }, 100 + i * 60);
        });
    });
</script>
@endpush
@endonce

@endsection

