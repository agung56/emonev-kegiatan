@extends('layouts.app')
@section('page_title', 'Master Pagu')

@section('content')
@php
    $canImportRkks = auth()->user()->role === 'admin';
    $canManagePagu = auth()->user()->role === 'admin';
@endphp
<div class="p-6 space-y-6" x-data="{ 
    openModal: @json($errors->any()), 
    editMode: @json(old('_method') === 'PUT'), 
    expandedRows: [],
    currentData: @js($errors->any() ? [
        'id' => old('id'),
        'program' => old('program', ''),
        'tahun_anggaran' => old('tahun_anggaran', now()->year),
        'total_nominal' => old('total_nominal', 0),
        'keterangan' => old('keterangan', ''),
        'komponens' => collect(old('komponen_anggaran', []))->values()->all(),
    ] : [
        'program' => '',
        'tahun_anggaran' => now()->year,
        'total_nominal' => 0,
        'keterangan' => '',
        'komponens' => [],
    ]),

    init() {
        this.currentData = this.normalizeCurrentData(this.currentData);
        this.calculateTotal();
    },

    emptyDetail() {
        return { id: null, detail: '', nominal: 0 };
    },

    emptySubKomponen() {
        return {
            sub_komponen: '',
            details: [this.emptyDetail()],
        };
    },

    emptyRoKomponen() {
        return {
            ro: '',
            komponen_label: '',
            sub_komponens: [this.emptySubKomponen()],
        };
    },

    emptyKomponen() {
        return {
            id: null,
            nama_kegiatan: '',
            ro_komponens: [this.emptyRoKomponen()],
        };
    },

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

    handleNominalInput(komponenIndex, roKomponenIndex, subKomponenIndex, detailIndex, val) {
        let numeric = val.replace(/[^0-9]/g, '');
        this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens[subKomponenIndex].details[detailIndex].nominal = numeric ? parseInt(numeric) : 0;
        this.calculateTotal();
    },

    toggleModal(edit = false, data = null) {
        this.editMode = edit;
        if (edit) {
            this.currentData = this.normalizeCurrentData(JSON.parse(JSON.stringify(data)));
        } else {
            this.currentData = this.normalizeCurrentData({});
        }
        this.calculateTotal();
        this.openModal = true;
    },

    normalizeCurrentData(data = {}) {
        return {
            id: data?.id ?? null,
            program: data?.program || data?.kegiatan || '',
            tahun_anggaran: data?.tahun_anggaran || new Date().getFullYear(),
            total_nominal: data?.total_nominal || 0,
            keterangan: data?.keterangan || '',
            komponens: this.normalizeKomponens(data?.komponens ?? [], data?.details ?? []),
        };
    },

    normalizeKomponens(komponens = [], legacyDetails = []) {
        const normalized = Array.isArray(komponens) && komponens.length
            ? komponens.map((komponen) => ({
                id: komponen?.id ?? null,
                nama_kegiatan: (komponen?.nama_kegiatan && String(komponen.nama_kegiatan).trim())
                    ? komponen.nama_kegiatan
                    : ((komponen?.nama_komponen && String(komponen.nama_komponen).trim())
                        ? komponen.nama_komponen
                        : ((Array.isArray(komponen?.details) && komponen.details.length) ? 'Kegiatan Utama' : '')),
                ro_komponens: this.normalizeRoKomponens(
                    komponen?.ro_komponens ?? [],
                    komponen?.sub_komponens ?? [],
                    komponen?.details ?? [],
                    komponen?.ro ?? '',
                    komponen?.komponen_label ?? '',
                ),
            }))
            : [];

        if (Array.isArray(legacyDetails) && legacyDetails.length) {
            const orphans = legacyDetails.filter((detail) => !detail?.pagu_komponen_id);
            if (orphans.length) {
                if (!normalized.length) {
                    normalized.push(this.emptyKomponen());
                    normalized[0].nama_kegiatan = 'Kegiatan Utama';
                }

                normalized[0].ro_komponens = [
                    ...normalized[0].ro_komponens.filter((item) => item.ro || item.komponen_label || item.sub_komponens.some((sub) => sub.sub_komponen || sub.details.some((detail) => detail.detail || detail.nominal))),
                    ...this.normalizeRoKomponens([], [], orphans),
                ];
            }
        }

        return normalized.length ? normalized : [this.emptyKomponen()];
    },

    normalizeRoKomponens(roKomponens = [], legacySubKomponens = [], legacyDetails = [], fallbackRo = '', fallbackKomponenLabel = '') {
        if (Array.isArray(roKomponens) && roKomponens.length) {
            const normalized = roKomponens.map((item) => ({
                ro: item?.ro ?? '',
                komponen_label: item?.komponen_label ?? '',
                sub_komponens: this.normalizeSubKomponens(item?.sub_komponens ?? [], item?.details ?? []),
            }));

            return normalized.length ? normalized : [this.emptyRoKomponen()];
        }

        if (Array.isArray(legacySubKomponens) && legacySubKomponens.length) {
            return [{
                ro: fallbackRo ?? '',
                komponen_label: fallbackKomponenLabel ?? '',
                sub_komponens: this.normalizeSubKomponens(legacySubKomponens, legacyDetails),
            }];
        }

        if (Array.isArray(legacyDetails) && legacyDetails.length) {
            const groups = [];

            legacyDetails.forEach((detail) => {
                const ro = detail?.ro ?? '';
                const komponenLabel = detail?.komponen_label ?? '';
                const key = `${ro}__${komponenLabel}`;

                let group = groups.find((item) => item.key === key);
                if (!group) {
                    group = {
                        key,
                        ro,
                        komponen_label: komponenLabel,
                        details: [],
                    };
                    groups.push(group);
                }

                group.details.push(detail);
            });

            return groups.map(({ key, details, ...group }) => ({
                ...group,
                sub_komponens: this.normalizeSubKomponens([], details),
            }));
        }

        return [this.emptyRoKomponen()];
    },

    normalizeSubKomponens(subKomponens = [], legacyDetails = []) {
        if (Array.isArray(subKomponens) && subKomponens.length) {
            const normalized = subKomponens.map((item) => ({
                sub_komponen: item?.sub_komponen ?? '',
                details: Array.isArray(item?.details) && item.details.length
                    ? item.details.map((detail) => ({
                        id: detail?.id ?? null,
                        detail: detail?.detail ?? detail?.nama_akun ?? '',
                        nominal: detail?.nominal ?? 0,
                    }))
                    : [this.emptyDetail()],
            }));

            return normalized.length ? normalized : [this.emptySubKomponen()];
        }

        if (Array.isArray(legacyDetails) && legacyDetails.length) {
            const groups = [];
            legacyDetails.forEach((detail) => {
                const key = detail?.sub_komponen ?? '';

                let group = groups.find((item) => item.key === key);
                if (!group) {
                    group = {
                        key,
                        sub_komponen: detail?.sub_komponen ?? '',
                        details: [],
                    };
                    groups.push(group);
                }

                group.details.push({
                    id: detail?.id ?? null,
                    detail: detail?.detail ?? detail?.nama_akun ?? '',
                    nominal: detail?.nominal ?? 0,
                });
            });

            return groups.map(({ key, ...group }) => ({
                ...group,
                details: group.details.length ? group.details : [this.emptyDetail()],
            }));
        }

        return [this.emptySubKomponen()];
    },

    addKomponen() {
        this.currentData.komponens.push(this.emptyKomponen());
    },

    removeKomponen(index) {
        if (this.currentData.komponens.length > 1) {
            this.currentData.komponens.splice(index, 1);
            this.calculateTotal();
        }
    },

    addRoKomponen(komponenIndex) {
        this.currentData.komponens[komponenIndex].ro_komponens.push(this.emptyRoKomponen());
    },

    removeRoKomponen(komponenIndex, roKomponenIndex) {
        if (this.currentData.komponens[komponenIndex].ro_komponens.length > 1) {
            this.currentData.komponens[komponenIndex].ro_komponens.splice(roKomponenIndex, 1);
            this.calculateTotal();
        }
    },

    addSubKomponen(komponenIndex, roKomponenIndex) {
        this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens.push(this.emptySubKomponen());
    },

    removeSubKomponen(komponenIndex, roKomponenIndex, subKomponenIndex) {
        if (this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens.length > 1) {
            this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens.splice(subKomponenIndex, 1);
            this.calculateTotal();
        }
    },

    addDetail(komponenIndex, roKomponenIndex, subKomponenIndex) {
        this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens[subKomponenIndex].details.push(this.emptyDetail());
    },

    removeDetail(komponenIndex, roKomponenIndex, subKomponenIndex, detailIndex) {
        if (this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens[subKomponenIndex].details.length > 1) {
            this.currentData.komponens[komponenIndex].ro_komponens[roKomponenIndex].sub_komponens[subKomponenIndex].details.splice(detailIndex, 1);
            this.calculateTotal();
        }
    },

    calculateTotal() {
        this.currentData.total_nominal = this.currentData.komponens.reduce((sum, komponen) => {
            return sum + (komponen.ro_komponens || []).reduce((roSum, roKomponen) => {
                return roSum + (roKomponen.sub_komponens || []).reduce((subSum, subKomponen) => {
                    return subSum + (subKomponen.details || []).reduce((detailSum, item) => {
                        return detailSum + (parseInt(item.nominal) || 0);
                    }, 0);
                }, 0);
            }, 0);
        }, 0);
    }
}">

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
    <div class="flex items-center gap-3 px-5 py-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-sm font-bold">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="flex flex-col gap-2 px-5 py-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-sm">
        <span class="font-black uppercase text-xs tracking-widest">Terdapat kesalahan saat menyimpan pagu:</span>
        @foreach($errors->all() as $error)
        <span class="font-medium">• {{ $error }}</span>
        @endforeach
    </div>
    @endif
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Master Pagu Anggaran</h1>
            <p class="text-sm text-slate-500 font-medium">Kelola program beserta rincian kegiatan, RO, komponen, dan detail anggarannya</p>
        </div>
        @if($canManagePagu)
        <button @click="toggleModal(false)" 
                class="flex items-center justify-center gap-2 px-6 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl shadow-lg hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH PAGU
        </button>
        @endif
    </div>

    <div class="grid grid-cols-1 {{ $canImportRkks ? 'xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1.4fr)]' : '' }} gap-4">
        @if($canImportRkks)
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Import RKKS Excel</h2>
                    <p class="text-xs text-slate-500 mt-1">Upload file RKKS Excel asli atau template .xlsx/.csv untuk membuat preview pengisian pagu otomatis.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-brand-primary/10 text-brand-primary uppercase">MVP</span>
            </div>
            <form action="{{ route('pagu.import.preview') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">File RKKS Excel</label>
                    <input type="file" name="rkks_excel" accept=".xlsx,.csv,.txt" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-white/10 rounded-2xl text-sm font-bold outline-none transition-all">
                </div>
                <div class="flex items-center justify-between gap-3 text-[11px] text-slate-500">
                    <span>Mendukung workbook RKKS bertingkat dan template kolom. Untuk template, kolom wajib: program, nama_kegiatan, ro, komponen, sub_komponen, detail, nominal.</span>
                    <button type="submit" class="shrink-0 px-5 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:opacity-90 transition-all">
                        Buat Preview
                    </button>
                </div>
            </form>
        </div>
        @endif

        @if($canImportRkks && $importPreview)
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Preview Import RKKS Excel</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ $importPreview['filename'] ?? 'File RKKS Excel' }} - TA {{ $importPreview['tahun_anggaran'] }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300 uppercase">{{ $importPreview['program_count'] }} Program</span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300 uppercase">{{ $importPreview['kegiatan_count'] }} Kegiatan</span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300 uppercase">{{ $importPreview['detail_count'] }} Detail</span>
                </div>
            </div>

            <div class="space-y-3 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                @foreach($importPreview['programs'] as $program)
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] space-y-3">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-black text-slate-800 dark:text-white">{{ $program['program'] }}</h3>
                            <p class="text-[11px] text-slate-500 mt-1">
                                {{ $program['kegiatan_count'] }} kegiatan · {{ $program['detail_count'] }} detail · Rp {{ number_format($program['total_nominal'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $program['match_status'] === 'existing' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' }}">
                            {{ $program['match_status'] === 'existing' ? 'Akan Update' : 'Program Baru' }}
                        </span>
                    </div>
                    @if($program['match_status'] === 'existing')
                    <p class="text-[11px] text-slate-500">Pagu existing terdeteksi: Rp {{ number_format($program['matched_total_nominal'] ?? 0, 0, ',', '.') }}</p>
                    @endif
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(collect($program['komponen_anggaran'] ?? [])->take(4) as $kegiatanPreview)
                        <span class="px-2 py-1 rounded-xl text-[10px] font-black bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ $kegiatanPreview['nama_kegiatan'] }}
                        </span>
                        @endforeach
                        @if(count($program['komponen_anggaran'] ?? []) > 4)
                        <span class="px-2 py-1 rounded-xl text-[10px] font-black bg-white dark:bg-slate-800 text-slate-400">
                            +{{ count($program['komponen_anggaran']) - 4 }} kegiatan
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <form action="{{ route('pagu.import.apply') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="mode" value="sync">
                    <button type="submit" class="w-full py-3 bg-brand-primary text-brand-black rounded-2xl text-[10px] font-black uppercase tracking-widest hover:brightness-110 transition-all">
                        Sinkronkan Dengan Data Existing
                    </button>
                </form>
                <form action="{{ route('pagu.import.apply') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="mode" value="create">
                    <button type="submit" class="w-full py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:opacity-90 transition-all">
                        Buat Program Baru
                    </button>
                </form>
                <form action="{{ route('pagu.import.clear') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full md:w-auto px-5 py-3 border border-slate-200 dark:border-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-red-500 transition-all">
                        Bersihkan
                    </button>
                </form>
            </div>
            <p class="text-[11px] text-slate-500">Mode sinkron akan memperbarui program dengan nama dan tahun yang sama, lalu membuat program baru bila belum ada.</p>
        </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
        <div class="relative col-span-1 md:col-span-2">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" id="searchInput" placeholder="Cari program, kegiatan, RO, komponen, atau detail..." 
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
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Program, Kegiatan & Tahun</th>
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
                                <span class="text-sm font-bold text-slate-800 dark:text-white uppercase leading-tight">{{ $pagu->program_label }}</span>
                                @if($pagu->komponens->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @foreach($pagu->komponens as $komponen)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                        {{ $komponen->nama_kegiatan_label }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                                <span class="text-[11px] text-brand-primary font-black uppercase italic mt-1">T.A {{ $pagu->tahun_anggaran }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 uppercase">
                                {{ $pagu->details->count() }} Detail Anggaran
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
                                @if($canManagePagu)
                                <button @click="toggleModal(true, @js($pagu))" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                @if($pagu->can_be_deleted)
                                <form action="{{ route('pagu.destroy', $pagu->id) }}" method="POST" data-confirm="Hapus pagu ini?" data-confirm-title="Hapus Pagu">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @else
                                <span class="p-2 text-slate-300 dark:text-slate-600 cursor-not-allowed" title="Pagu tidak bisa dihapus karena sudah dipakai pada kegiatan atau anggaran.">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </span>
                                @endif
                                @endif
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
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Hierarki</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Detail</th>
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
                                            <td class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                                                {{ $komponen->nama_kegiatan_label }}
                                                @if($det->ro || $det->komponen_label || $det->sub_komponen)
                                                    <span class="block text-[11px] font-medium italic text-slate-400 dark:text-slate-500">
                                                        {{ collect([$det->ro, $det->komponen_label, $det->sub_komponen])->filter()->join(' / ') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ $det->detail_label }}</td>
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
                                            <td class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 italic">
                                                {{ collect([$det->ro, $det->komponen_label, $det->sub_komponen])->filter()->join(' / ') ?: 'Belum dipetakan' }}
                                            </td>
                                            <td class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ $det->detail_label }}</td>
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
    @if($canManagePagu)
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
                        <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight" x-text="editMode ? 'Edit Program Pagu' : 'Input Program Pagu'"></h3>
                        <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Formulir Program dan Rincian Anggaran</p>
                    </div>
                    <button @click="openModal = false" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="editMode ? `/pagu/${currentData.id}` : '{{ route('pagu.store') }}'" method="POST" class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="id" :value="currentData.id || ''">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Program</label>
                            <input type="text" name="program" x-model="currentData.program" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all">
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Daftar Kegiatan</label>
                                <button type="button" @click="addKomponen()" class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Kegiatan
                                </button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(komponen, index) in currentData.komponens" :key="`komponen-${index}`">
                                    <div class="space-y-4 p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/5">
                                        <input type="hidden" :name="`komponen_anggaran[${index}][id]`" :value="komponen.id || ''">
                                        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto] gap-3 items-start">
                                            <input type="text" :name="`komponen_anggaran[${index}][nama_kegiatan]`" x-model="komponen.nama_kegiatan" placeholder="Contoh: Pelatihan Keuangan Daerah" class="w-full bg-white dark:bg-slate-800 border-none px-4 py-3 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold placeholder:font-medium placeholder:text-slate-400">
                                            <button type="button" @click="removeKomponen(index)" class="p-2.5 text-slate-300 hover:text-red-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between px-1">
                                                <h5 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">RO dan Komponen</h5>
                                                <button type="button" @click="addRoKomponen(index)" class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                    Tambah RO/Komponen
                                                </button>
                                            </div>
                                            <template x-for="(roKomponen, roIndex) in komponen.ro_komponens" :key="`ro-${index}-${roIndex}`">
                                                <div class="space-y-4 p-4 bg-white dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-white/5 hover:border-brand-primary/50 transition-all">
                                                    <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] gap-3 items-start">
                                                        <input type="text" :name="`komponen_anggaran[${index}][ro_komponens][${roIndex}][ro]`" x-model="roKomponen.ro" placeholder="RO..." required class="w-full bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold placeholder:font-medium placeholder:text-slate-400">
                                                        <input type="text" :name="`komponen_anggaran[${index}][ro_komponens][${roIndex}][komponen_label]`" x-model="roKomponen.komponen_label" placeholder="Komponen..." required class="w-full bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold placeholder:font-medium placeholder:text-slate-400">
                                                        <button type="button" @click="removeRoKomponen(index, roIndex)" class="p-2.5 text-slate-300 hover:text-red-500 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </div>
                                                    <div class="space-y-3">
                                                        <div class="flex items-center justify-between px-1">
                                                            <h6 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sub Komponen</h6>
                                                            <button type="button" @click="addSubKomponen(index, roIndex)" class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                                Tambah Sub Komponen
                                                            </button>
                                                        </div>
                                                        <template x-for="(subKomponen, subIndex) in roKomponen.sub_komponens" :key="`sub-${index}-${roIndex}-${subIndex}`">
                                                            <div class="space-y-4 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-white/5">
                                                                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto] gap-3">
                                                                    <div class="flex gap-3">
                                                                        <input type="text" :name="`komponen_anggaran[${index}][ro_komponens][${roIndex}][sub_komponens][${subIndex}][sub_komponen]`" x-model="subKomponen.sub_komponen" placeholder="Sub komponen..." required class="w-full bg-white dark:bg-slate-900 border-none px-3 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold">
                                                                        <button type="button" @click="removeSubKomponen(index, roIndex, subIndex)" class="p-2.5 text-slate-300 hover:text-red-500 transition-colors">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="space-y-3">
                                                                    <div class="flex items-center justify-between px-1">
                                                                        <h6 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Detail Anggaran</h6>
                                                                        <button type="button" @click="addDetail(index, roIndex, subIndex)" class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                                            Tambah Detail
                                                                        </button>
                                                                    </div>
                                                                    <template x-for="(detail, detailIndex) in subKomponen.details" :key="`detail-${index}-${roIndex}-${subIndex}-${detailIndex}`">
                                                                        <div class="flex flex-col md:flex-row gap-3 items-start">
                                                                            <input type="hidden" :name="`komponen_anggaran[${index}][ro_komponens][${roIndex}][sub_komponens][${subIndex}][details][${detailIndex}][id]`" :value="detail.id || ''">
                                                                            <div class="flex-1 w-full">
                                                                                <input type="text" :name="`komponen_anggaran[${index}][ro_komponens][${roIndex}][sub_komponens][${subIndex}][details][${detailIndex}][detail]`" x-model="detail.detail" placeholder="Detail anggaran..." required class="w-full bg-white dark:bg-slate-900 border-none px-3 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold">
                                                                            </div>
                                                                            <div class="w-full md:w-48">
                                                                                <input type="text" :value="formatRupiah(detail.nominal)" @input="handleNominalInput(index, roIndex, subIndex, detailIndex, $event.target.value)" placeholder="Rp 0" class="w-full bg-white dark:bg-slate-900 border-none px-3 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-black text-right text-brand-primary">
                                                                                <input type="hidden" :name="`komponen_anggaran[${index}][ro_komponens][${roIndex}][sub_komponens][${subIndex}][details][${detailIndex}][nominal]`" :value="detail.nominal">
                                                                            </div>
                                                                            <button type="button" @click="removeDetail(index, roIndex, subIndex, detailIndex)" class="p-2.5 text-slate-300 hover:text-red-500 transition-colors">
                                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
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
    @endif
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



