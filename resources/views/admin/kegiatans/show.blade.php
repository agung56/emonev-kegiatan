@extends('layouts.app')
@section('page_title', 'Detail Kegiatan')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('kegiatans.index') }}"
               class="p-2.5 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight leading-tight">{{ $kegiatan->nama_kegiatan }}</h1>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ $kegiatan->kepemilikan === 'lembaga' ? 'bg-purple-500/10 text-purple-600' : 'bg-orange-500/10 text-orange-600' }}">
                        {{ $kegiatan->kepemilikan }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400">
                        TA {{ $kegiatan->tahun_anggaran }}
                    </span>
                    @if($kegiatan->lokus)
                    <span class="text-xs text-slate-400 font-medium">📍 {{ $kegiatan->lokus }}</span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('kegiatans.edit', $kegiatan->id) }}"
           class="flex items-center justify-center gap-2 px-6 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl shadow-lg shadow-brand-primary/20 hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            EDIT KEGIATAN
        </a>
    </div>

    {{-- Info Cards Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 p-4 flex flex-col gap-1">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal Mulai</span>
            <span class="text-sm font-black text-slate-800 dark:text-white">{{ $kegiatan->tanggal_mulai->format('d M Y') }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 p-4 flex flex-col gap-1">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal Selesai</span>
            <span class="text-sm font-black text-slate-800 dark:text-white">{{ $kegiatan->tanggal_selesai->format('d M Y') }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 p-4 flex flex-col gap-1">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Durasi</span>
            <span class="text-sm font-black text-slate-800 dark:text-white">{{ $kegiatan->tanggal_mulai->diffInDays($kegiatan->tanggal_selesai) + 1 }} Hari</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 p-4 flex flex-col gap-1">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Anggaran</span>
            <span class="text-sm font-black text-brand-primary">Rp {{ number_format($kegiatan->anggarans->sum('nominal_digunakan'), 0, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 p-4 flex flex-col gap-1">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sub Bagian Pelaksana</span>
            <span class="text-sm font-black text-slate-800 dark:text-white">{{ $kegiatan->subBagianPelaksana->nama_sub_bagian ?? $kegiatan->createdBy?->subBagian?->nama_sub_bagian ?? '-' }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Sasaran & Indikator --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                    <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Sasaran & Indikator</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sasaran</span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $kegiatan->sasaran->nama_sasaran ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Indikator ({{ $kegiatan->indikators->count() }})</span>
                        @forelse($kegiatan->indikators as $ind)
                        <div class="flex items-start gap-2.5 p-3 bg-blue-50/50 dark:bg-blue-500/5 rounded-2xl">
                            <div class="w-1.5 h-1.5 rounded-full bg-brand-primary mt-1.5 shrink-0"></div>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $ind->nama_indikator }}</span>
                        </div>
                        @empty
                        <span class="text-sm text-slate-400 italic">Tidak ada indikator terpilih.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Penggunaan Anggaran --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                    <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Penggunaan Anggaran</h2>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Pagu: {{ $kegiatan->pagu?->kegiatan ?? '-' }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-white/5">
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Komponen</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Nama Akun</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Pagu Akun</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] text-right">Digunakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($kegiatan->anggarans as $ang)
                            <tr>
                                <td class="px-6 py-3 text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $ang->paguDetail->komponen?->nama_komponen ?? '-' }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $ang->paguDetail->nama_akun ?? '-' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-500">Rp {{ number_format($ang->paguDetail->nominal ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-sm font-black text-brand-primary text-right">Rp {{ number_format($ang->nominal_digunakan, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-6 text-center text-sm text-slate-400 italic">Belum ada data anggaran.</td></tr>
                            @endforelse
                        </tbody>
                        @if($kegiatan->anggarans->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-50 dark:bg-white/5">
                                <td colspan="3" class="px-6 py-3 text-xs font-black text-slate-500 uppercase tracking-widest text-right">Total</td>
                                <td class="px-6 py-3 text-sm font-black text-brand-primary text-right">Rp {{ number_format($kegiatan->anggarans->sum('nominal_digunakan'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Output & Kendala --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                        <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Output Kegiatan</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
                            {{ $kegiatan->output_kegiatan ?? '—' }}
                        </p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                        <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Kendala Kegiatan</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
                            {{ $kegiatan->kendala_kegiatan ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan (1/3) --}}
        <div class="space-y-6">

            {{-- Dokumentasi --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                    <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Dokumentasi</h2>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $kegiatan->dokumens->count() }} file</p>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($kegiatan->dokumens as $dok)
                    <a href="{{ Storage::url($dok->path_file) }}" target="_blank"
                       class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all group/dok">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                            {{ $dok->tipe_file === 'pdf' ? 'bg-red-100 text-red-500' : '' }}
                            {{ $dok->tipe_file === 'image' ? 'bg-blue-100 text-blue-500' : '' }}
                            {{ $dok->tipe_file === 'word' ? 'bg-indigo-100 text-indigo-500' : '' }}
                            {{ $dok->tipe_file === 'excel' ? 'bg-green-100 text-green-500' : '' }}">
                            @if($dok->tipe_file === 'pdf')
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6l-4-4H4v16zm8-14l2 2h-2V4z"/></svg>
                            @elseif($dok->tipe_file === 'image')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate group-hover/dok:text-brand-primary transition-colors">{{ $dok->nama_file }}</p>
                            <p class="text-[10px] text-slate-400 font-medium uppercase">
                                {{ strtoupper($dok->tipe_file) }}
                                @if($dok->ukuran_file)
                                • {{ number_format($dok->ukuran_file / 1024 / 1024, 2) }} MB
                                @endif
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover/dok:text-brand-primary transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @empty
                    <div class="flex flex-col items-center gap-2 py-8">
                        <svg class="w-8 h-8 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Belum ada dokumen</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Meta Info --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm p-5 space-y-3">
                <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Pencatatan</h2>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Dibuat oleh</span>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $kegiatan->createdBy->name ?? '-' }}</span>
                            @if($kegiatan->createdBy?->subBagian)
                            <p class="text-[10px] text-brand-primary font-black mt-0.5">{{ $kegiatan->createdBy->subBagian->nama_sub_bagian }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Pelaksana</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $kegiatan->subBagianPelaksana->nama_sub_bagian ?? $kegiatan->createdBy?->subBagian?->nama_sub_bagian ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Dibuat pada</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $kegiatan->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Diperbarui</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $kegiatan->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
