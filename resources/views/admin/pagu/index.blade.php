@extends('layouts.app')
@section('page_title', 'Master Pagu')

@section('content')
<div class="p-6 space-y-6" x-data="{ 
    openModal: false, 
    editMode: false, 
    expandedRows: [],
    currentData: { komponens: [] },

    toggleRow(id) {
        if (this.expandedRows.includes(id)) {
            this.expandedRows = this.expandedRows.filter(i => i !== id);
        } else {
            this.expandedRows.push(id);
        }
    },

    formatRupiah(value) {
        if (!value && value !== 0) return 'Rp 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    },

    handleNominalInput(komponenIndex, detailIndex, val) {
        let numeric = val.replace(/[^0-9]/g, '');
        this.currentData.komponens[komponenIndex].details[detailIndex].nominal = numeric ? parseInt(numeric) : 0;
        this.calculateTotal();
    },

    toggleModal(edit = false, data = null) {
        this.editMode = edit;
        if (edit) {
            this.currentData = JSON.parse(JSON.stringify(data));
            this.currentData.komponens = this.normalizeKomponens(this.currentData.komponens, this.currentData.details);
        } else {
            this.currentData = { 
                kegiatan: '', 
                komponens: [{ id: null, nama_komponen: '', details: [{ id: null, nama_akun: '', nominal: 0 }] }],
                tahun_anggaran: new Date().getFullYear(), 
                total_nominal: 0, 
                keterangan: '', 
            };
        }
        this.calculateTotal();
        this.openModal = true;
    },

    normalizeKomponens(komponens = [], legacyDetails = []) {
        if (!Array.isArray(komponens) || komponens.length === 0) {
            return [{
                id: null,
                nama_komponen: '',
                details: Array.isArray(legacyDetails) && legacyDetails.length
                    ? legacyDetails.map((detail) => ({
                        id: detail?.id ?? null,
                        nama_akun: detail?.nama_akun ?? '',
                        nominal: detail?.nominal ?? 0,
                    }))
                    : [{ id: null, nama_akun: '', nominal: 0 }]
            }];
        }

        const normalized = komponens.map((komponen) => ({
            id: komponen?.id ?? null,
            nama_komponen: komponen?.nama_komponen ?? '',
            details: Array.isArray(komponen?.details) && komponen.details.length
                ? komponen.details.map((detail) => ({
                    id: detail?.id ?? null,
                    nama_akun: detail?.nama_akun ?? '',
                    nominal: detail?.nominal ?? 0,
                }))
                : [{ id: null, nama_akun: '', nominal: 0 }]
        }));

        if (Array.isArray(legacyDetails) && legacyDetails.length) {
            const orphans = legacyDetails.filter((detail) => !detail?.pagu_komponen_id);
            if (orphans.length) {
                normalized[0].details = [
                    ...normalized[0].details.filter((detail) => detail.nama_akun || detail.nominal),
                    ...orphans.map((detail) => ({
                        id: detail?.id ?? null,
                        nama_akun: detail?.nama_akun ?? '',
                        nominal: detail?.nominal ?? 0,
                    }))
                ];
            }
        }

        return normalized;
    },

    addKomponen() {
        this.currentData.komponens.push({ id: null, nama_komponen: '', details: [{ id: null, nama_akun: '', nominal: 0 }] });
    },

    removeKomponen(index) {
        if (this.currentData.komponens.length > 1) {
            this.currentData.komponens.splice(index, 1);
            this.calculateTotal();
        }
    },

    addDetail(komponenIndex) {
        this.currentData.komponens[komponenIndex].details.push({ id: null, nama_akun: '', nominal: 0 });
    },

    removeDetail(komponenIndex, detailIndex) {
        if(this.currentData.komponens[komponenIndex].details.length > 1) {
            this.currentData.komponens[komponenIndex].details.splice(detailIndex, 1);
            this.calculateTotal();
        }
    },

    calculateTotal() {
        this.currentData.total_nominal = this.currentData.komponens.reduce((sum, komponen) => {
            return sum + (komponen.details || []).reduce((detailSum, item) => {
                return detailSum + (parseInt(item.nominal) || 0);
            }, 0);
        }, 0);
    }
}">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Master Pagu Anggaran</h1>
            <p class="text-sm text-slate-500 font-medium">Manajemen alokasi anggaran kegiatan dan rincian belanja</p>
        </div>
        <button @click="toggleModal(false)" 
                class="flex items-center justify-center gap-2 px-6 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl shadow-lg hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH PAGU
        </button>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
        <div class="relative col-span-1 md:col-span-2">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" id="searchInput" placeholder="Cari kegiatan, komponen, atau nama akun..." 
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all">
        </div>
        <div>
            <select id="yearFilter" class="w-full px-4 py-3 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                <option value="">Semua Tahun</option>
                @foreach($pagus->pluck('tahun_anggaran')->unique()->sortDesc() as $year)
                    <option value="{{ $year }}">Tahun {{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0" id="paguTable">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5">
                        <th class="w-12 px-6 py-5"></th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kegiatan, Komponen & Tahun</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status Rincian</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Total Pagu</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Terpakai</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Sisa Pagu</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach($pagus as $pagu)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4 text-center">
                            <button @click="toggleRow({{ $pagu->id }})" class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                                <svg class="w-4 h-4 transition-transform duration-300" :class="expandedRows.includes({{ $pagu->id }}) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800 dark:text-white uppercase leading-tight">{{ $pagu->kegiatan }}</span>
                                @if($pagu->komponens->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @foreach($pagu->komponens as $komponen)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                        {{ $komponen->nama_komponen }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                                <span class="text-[11px] text-brand-primary font-black uppercase italic mt-1">T.A {{ $pagu->tahun_anggaran }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 uppercase">
                                {{ $pagu->details->count() }} Akun Belanja
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-black text-brand-primary">Rp {{ number_format($pagu->total_nominal, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-black text-amber-500">Rp {{ number_format($pagu->total_terpakai ?? 0, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @php $sisa = $pagu->sisa_pagu ?? 0; @endphp
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-black {{ $sisa < 0 ? 'text-red-500' : 'text-green-500' }}">
                                    Rp {{ number_format($sisa, 0, ',', '.') }}
                                </span>
                                @php $pct = $pagu->total_nominal > 0 ? ($sisa / $pagu->total_nominal * 100) : 0; @endphp
                                <div class="w-20 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full mt-1.5 overflow-hidden">
                                    <div class="h-full rounded-full transition-all {{ $sisa < 0 ? 'bg-red-400' : ($pct < 30 ? 'bg-amber-400' : 'bg-green-400') }}"
                                         style="width: {{ max(0, min(100, $pct)) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="toggleModal(true, {{ $pagu->toJson() }})" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form action="{{ route('pagu.destroy', $pagu->id) }}" method="POST" onsubmit="return confirm('Hapus pagu?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    
                    {{-- Sub Table (Expanded) --}}
                    <tr x-show="expandedRows.includes({{ $pagu->id }})" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="bg-slate-50/30 dark:bg-white/[0.02]">
                        <td colspan="7" class="px-12 py-4">
                            <div class="overflow-hidden rounded-2xl border border-slate-100 dark:border-white/5 bg-white dark:bg-slate-800/50 shadow-inner">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 dark:bg-white/5">
                                        <tr>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Komponen</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Akun Belanja</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Alokasi</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Terpakai</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Sisa</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Penyerapan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                                        @foreach($pagu->komponens as $komponen)
                                        @foreach($komponen->details as $det)
                                        @php
                                            $detTerpakai = $det->terpakai ?? 0;
                                            $detSisa     = $det->sisa ?? $det->nominal;
                                            $detPct      = $det->nominal > 0 ? ($detTerpakai / $det->nominal * 100) : 0;
                                        @endphp
                                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400">{{ $komponen->nama_komponen }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ $det->nama_akun }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-slate-700 dark:text-slate-200 text-right whitespace-nowrap">Rp {{ number_format($det->nominal, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-amber-500 text-right whitespace-nowrap">Rp {{ number_format($detTerpakai, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-right whitespace-nowrap {{ $detSisa < 0 ? 'text-red-500' : 'text-green-500' }}">Rp {{ number_format($detSisa, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full {{ $detPct >= 90 ? 'bg-red-400' : ($detPct >= 60 ? 'bg-amber-400' : 'bg-green-400') }}"
                                                             style="width: {{ min(100, $detPct) }}%"></div>
                                                    </div>
                                                    <span class="text-[10px] font-black {{ $detPct >= 90 ? 'text-red-500' : ($detPct >= 60 ? 'text-amber-500' : 'text-green-500') }} w-10 text-right">{{ number_format($detPct, 1) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                        @foreach($pagu->details->whereNull('pagu_komponen_id') as $det)
                                        @php
                                            $detTerpakai = $det->terpakai ?? 0;
                                            $detSisa     = $det->sisa ?? $det->nominal;
                                            $detPct      = $det->nominal > 0 ? ($detTerpakai / $det->nominal * 100) : 0;
                                        @endphp
                                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 italic">Belum dipetakan</td>
                                            <td class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ $det->nama_akun }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-slate-700 dark:text-slate-200 text-right whitespace-nowrap">Rp {{ number_format($det->nominal, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-amber-500 text-right whitespace-nowrap">Rp {{ number_format($detTerpakai, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-right whitespace-nowrap {{ $detSisa < 0 ? 'text-red-500' : 'text-green-500' }}">Rp {{ number_format($detSisa, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full {{ $detPct >= 90 ? 'bg-red-400' : ($detPct >= 60 ? 'bg-amber-400' : 'bg-green-400') }}"
                                                             style="width: {{ min(100, $detPct) }}%"></div>
                                                    </div>
                                                    <span class="text-[10px] font-black {{ $detPct >= 90 ? 'text-red-500' : ($detPct >= 60 ? 'text-amber-500' : 'text-green-500') }} w-10 text-right">{{ number_format($detPct, 1) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        {{-- Footer total per pagu --}}
                                        <tr class="bg-slate-50 dark:bg-white/5 border-t-2 border-slate-200 dark:border-white/10">
                                            <td colspan="2" class="px-4 py-2.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">TOTAL</td>
                                            <td class="px-4 py-2.5 text-[10px] font-black text-slate-700 dark:text-white text-right">Rp {{ number_format($pagu->total_nominal, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2.5 text-[10px] font-black text-amber-500 text-right">Rp {{ number_format($pagu->total_terpakai ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2.5 text-[10px] font-black text-right {{ ($pagu->sisa_pagu ?? 0) < 0 ? 'text-red-500' : 'text-green-500' }}">Rp {{ number_format($pagu->sisa_pagu ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2.5 text-[10px] font-black text-slate-400 text-center">
                                                @php $totalPct = $pagu->total_nominal > 0 ? (($pagu->total_terpakai ?? 0) / $pagu->total_nominal * 100) : 0; @endphp
                                                {{ number_format($totalPct, 1) }}% terserap
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal (Sama seperti sebelumnya dengan sedikit perapihan) --}}
    <template x-teleport="body">
        <div x-show="openModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-cloak>
            <div @click="openModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div class="relative bg-white dark:bg-slate-900 w-full max-w-3xl rounded-[2.5rem] shadow-2xl border border-white/10 overflow-hidden"
                 x-show="openModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                
                <div class="px-8 py-6 border-b dark:border-white/5 flex items-center justify-between bg-slate-50/50 dark:bg-white/5">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight" x-text="editMode ? 'Edit Pagu Anggaran' : 'Input Pagu Baru'"></h3>
                        <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Formulir Rencana Anggaran</p>
                    </div>
                    <button @click="openModal = false" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="editMode ? `/pagu/${currentData.id}` : '{{ route('pagu.store') }}'" method="POST" class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Kegiatan</label>
                            <input type="text" name="kegiatan" x-model="currentData.kegiatan" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all">
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Komponen Anggaran</label>
                                <button type="button" @click="addKomponen()" class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Komponen
                                </button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(komponen, index) in currentData.komponens" :key="`komponen-${index}`">
                                    <div class="space-y-4 p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/5">
                                        <input type="hidden" :name="`komponen_anggaran[${index}][id]`" :value="komponen.id || ''">
                                        <div class="flex items-center gap-3">
                                            <input type="text" :name="`komponen_anggaran[${index}][nama_komponen]`" x-model="komponen.nama_komponen" placeholder="Contoh: Belanja Barang dan Jasa" class="w-full bg-white dark:bg-slate-800 border-none px-4 py-3 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold placeholder:font-medium placeholder:text-slate-400">
                                            <button type="button" @click="removeKomponen(index)" class="p-2.5 text-slate-300 hover:text-red-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between px-1">
                                                <h5 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Rincian Per Akun</h5>
                                                <button type="button" @click="addDetail(index)" class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                    Tambah Item Akun
                                                </button>
                                            </div>
                                            <template x-for="(detail, detailIndex) in komponen.details" :key="`detail-${index}-${detailIndex}`">
                                                <div class="group flex flex-col md:flex-row gap-3 p-4 bg-white dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-white/5 hover:border-brand-primary/50 transition-all">
                                                    <input type="hidden" :name="`komponen_anggaran[${index}][details][${detailIndex}][id]`" :value="detail.id || ''">
                                                    <div class="flex-1">
                                                        <input type="text" :name="`komponen_anggaran[${index}][details][${detailIndex}][nama_akun]`" x-model="detail.nama_akun" placeholder="Nama Akun Belanja..." required 
                                                            class="w-full bg-slate-50 dark:bg-slate-800 border-none px-3 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold">
                                                    </div>
                                                    <div class="w-full md:w-48">
                                                        <input type="text" :value="formatRupiah(detail.nominal)" @input="handleNominalInput(index, detailIndex, $event.target.value)" placeholder="Rp 0" 
                                                            class="w-full bg-slate-50 dark:bg-slate-800 border-none px-3 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-black text-right text-brand-primary">
                                                        <input type="hidden" :name="`komponen_anggaran[${index}][details][${detailIndex}][nominal]`" :value="detail.nominal">
                                                    </div>
                                                    <button type="button" @click="removeDetail(index, detailIndex)" class="p-2.5 text-slate-300 hover:text-red-500 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun Anggaran</label>
                            <input type="number" name="tahun_anggaran" x-model="currentData.tahun_anggaran" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Total Pagu Terkalkulasi</label>
                            <div class="w-full px-5 py-3.5 bg-slate-100 dark:bg-slate-700/50 border-2 border-transparent rounded-2xl text-sm font-black text-brand-primary flex items-center h-[52px]" x-text="formatRupiah(currentData.total_nominal)"></div>
                            <input type="hidden" name="total_nominal" :value="currentData.total_nominal">
                        </div>
                    </div>

                    <div class="mt-8 flex gap-4 sticky bottom-0 bg-white dark:bg-slate-900 pt-4 border-t dark:border-white/5">
                        <button type="button" @click="openModal = false" class="flex-1 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all">Batal</button>
                        <button type="submit" class="flex-[2] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-brand-primary/20 uppercase tracking-widest">Simpan Data Pagu</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const yearFilter = document.getElementById('yearFilter');
        const tableBody = document.querySelector('#paguTable tbody');
        
        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedYear = yearFilter.value;
            const rows = tableBody.querySelectorAll('tr[class*="group"]'); // Hanya baris utama

            rows.forEach(row => {
                const kegiatanText = row.cells[1].innerText.toLowerCase();
                const yearBadge = row.cells[1].querySelector('.italic');
                const yearText = yearBadge ? yearBadge.innerText.replace('T.A ', '') : '';
                
                // Pencarian juga mengecek sub-table (detail akun) yang ada di bawah baris ini
                const subTable = row.nextElementSibling;
                const subTableText = subTable ? subTable.innerText.toLowerCase() : '';

                const matchesSearch = kegiatanText.includes(searchTerm) || subTableText.includes(searchTerm);
                const matchesYear = selectedYear === "" || yearText === selectedYear;

                if (matchesSearch && matchesYear) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    // Jika baris utama sembunyi, sub-table juga harus sembunyi (handled by Alpine x-show biasanya, tapi ini untuk filter manual)
                }
            });
        }

        searchInput.addEventListener('input', filterTable);
        yearFilter.addEventListener('change', filterTable);
    });
</script>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
@endsection
