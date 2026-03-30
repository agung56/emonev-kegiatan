@extends('layouts.app')
@section('page_title', 'Manajemen Kegiatan')

@section('content')
<div class="p-6 space-y-6">

    {{-- Flash Message --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="flex items-center gap-3 px-5 py-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 text-green-700 dark:text-green-400 rounded-2xl text-sm font-bold">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="flex items-center gap-3 px-5 py-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-sm font-bold">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Manajemen Kegiatan</h1>
            <p class="text-sm text-slate-500 font-medium">Pencatatan dan pemantauan pelaksanaan kegiatan</p>
        </div>
        <a href="{{ route('kegiatans.create') }}"
           class="flex items-center justify-center gap-2 px-6 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl shadow-lg shadow-brand-primary/20 hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH KEGIATAN
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 p-4 shadow-sm">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" id="kegiatanSearch" onkeyup="searchTable()"
                       class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary/50 rounded-2xl text-sm focus:ring-0 text-slate-700 dark:text-slate-200 transition-all font-semibold placeholder:text-slate-400 shadow-inner"
                       placeholder="Cari nama kegiatan, lokus, atau sasaran...">
            </div>
            <div class="relative">
                <select id="filterTahun" onchange="searchTable()"
                        class="appearance-none pl-4 pr-10 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary/50 rounded-2xl text-sm text-slate-700 dark:text-slate-200 font-bold outline-none cursor-pointer transition-all">
                    <option value="">Semua Tahun</option>
                    @foreach($tahuns as $tahun)
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            <div class="relative">
                <select id="filterKepemilikan" onchange="searchTable()"
                        class="appearance-none pl-4 pr-10 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary/50 rounded-2xl text-sm text-slate-700 dark:text-slate-200 font-bold outline-none cursor-pointer transition-all">
                    <option value="">Semua Kepemilikan</option>
                    <option value="lembaga">Lembaga</option>
                    <option value="sekretariat">Sekretariat</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="kegiatanTable">
                <thead>
                    <tr class="bg-slate-50 dark:bg-white/5">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kegiatan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Sasaran & Indikator</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pagu / Anggaran</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Waktu</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($kegiatans as $kegiatan)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group search-row"
                        data-nama="{{ strtolower($kegiatan->nama_kegiatan) }}"
                        data-lokus="{{ strtolower($kegiatan->lokus) }}"
                        data-sasaran="{{ strtolower($kegiatan->sasaran->nama_sasaran ?? '') }}"
                        data-tahun="{{ $kegiatan->tahun_anggaran }}"
                        data-kepemilikan="{{ $kegiatan->kepemilikan }}">
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-brand-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-800 dark:text-white tracking-tight leading-tight">{{ $kegiatan->nama_kegiatan }}</span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ $kegiatan->kepemilikan === 'lembaga' ? 'bg-purple-500/10 text-purple-600' : 'bg-orange-500/10 text-orange-600' }}">
                                            {{ $kegiatan->kepemilikan }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400">
                                            {{ $kegiatan->tahun_anggaran }}
                                        </span>
                                        @if($kegiatan->lokus)
                                        <span class="text-[10px] text-slate-400 font-medium">📍 {{ $kegiatan->lokus }}</span>
                                        @endif
                                    </div>
                                    @if($kegiatan->subBagianPelaksana || $kegiatan->createdBy?->subBagian)
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $kegiatan->subBagianPelaksana->nama_sub_bagian ?? $kegiatan->createdBy?->subBagian?->nama_sub_bagian }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $kegiatan->sasaran->nama_sasaran ?? '-' }}</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($kegiatan->indikators->take(2) as $ind)
                                    <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg text-[9px] font-bold">{{ Str::limit($ind->nama_indikator, 30) }}</span>
                                    @endforeach
                                    @if($kegiatan->indikators->count() > 2)
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-white/10 text-slate-500 rounded-lg text-[9px] font-bold">+{{ $kegiatan->indikators->count() - 2 }} lagi</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $kegiatan->pagu?->program_label ?? '-' }}</span>
                                @php $totalAnggaran = $kegiatan->anggarans->sum('nominal_digunakan'); @endphp
                                <span class="text-[11px] font-black text-brand-primary">Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $kegiatan->tanggal_mulai->format('d M Y') }}</span>
                                <span class="text-[10px] text-slate-400">s/d</span>
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $kegiatan->tanggal_selesai->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kegiatans.show', $kegiatan->id) }}"
                                   class="p-2.5 text-green-500 hover:bg-green-50 dark:hover:bg-green-500/10 rounded-xl transition-all" title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('kegiatans.edit', $kegiatan->id) }}"
                                   class="p-2.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                @if(auth()->user()->role === 'admin')
                                <form action="{{ route('kegiatans.destroy', $kegiatan->id) }}" method="POST" data-confirm="Hapus kegiatan ini?" data-confirm-title="Hapus Kegiatan">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all cursor-pointer" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Belum ada kegiatan</p>
                                <a href="{{ route('kegiatans.create') }}" class="text-xs font-black text-brand-primary hover:underline">+ Tambah Kegiatan Pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kegiatans->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 dark:border-white/5">
            {{ $kegiatans->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function searchTable() {
    const keyword = document.getElementById('kegiatanSearch').value.toLowerCase();
    const tahun = document.getElementById('filterTahun').value;
    const kepemilikan = document.getElementById('filterKepemilikan').value.toLowerCase();
    const rows = document.querySelectorAll('.search-row');

    rows.forEach(row => {
        const matchKeyword = !keyword ||
            row.dataset.nama.includes(keyword) ||
            row.dataset.lokus.includes(keyword) ||
            row.dataset.sasaran.includes(keyword);
        const matchTahun = !tahun || row.dataset.tahun === tahun;
        const matchKepemilikan = !kepemilikan || row.dataset.kepemilikan === kepemilikan;
        row.style.display = (matchKeyword && matchTahun && matchKepemilikan) ? '' : 'none';
    });
}
</script>
@endsection
