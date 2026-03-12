@extends('layouts.app')
@section('page_title', 'Master Sasaran')

@section('content')
<div class="p-6 space-y-6" x-data="{
    openModal: false,
    editMode: false,
    expandedRows: [],
    currentData: { indikators: [] },

    toggleRow(id) {
        if (this.expandedRows.includes(id)) {
            this.expandedRows = this.expandedRows.filter(i => i !== id);
        } else {
            this.expandedRows.push(id);
        }
    },

    toggleModal(edit = false, data = null) {
        this.editMode = edit;
        if (edit && data) {
            this.currentData = JSON.parse(JSON.stringify(data));
            if (!this.currentData.indikators) this.currentData.indikators = [];
        } else {
            this.currentData = {
                nama_sasaran: '',
                kepemilikan: 'lembaga',
                tahun_anggaran: new Date().getFullYear(),
                is_aktif: true,
                indikators: [{ nama_indikator: '' }]
            };
        }
        this.openModal = true;
    },

    addIndikator() {
        this.currentData.indikators.push({ nama_indikator: '' });
    },

    removeIndikator(index) {
        if (this.currentData.indikators.length > 1) {
            this.currentData.indikators.splice(index, 1);
        }
    }
}">

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

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Master Sasaran</h1>
            <p class="text-sm text-slate-500 font-medium">Manajemen sasaran strategis dan indikator kinerja</p>
        </div>
        <button @click="toggleModal(false)"
                class="flex items-center justify-center gap-2 px-6 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl shadow-lg hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH SASARAN
        </button>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
        <div class="relative col-span-1 md:col-span-2">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" id="searchInput" placeholder="Cari nama sasaran atau indikator..."
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all">
        </div>
        <div>
            <select id="yearFilter" class="w-full px-4 py-3 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                <option value="">Semua Tahun</option>
                @foreach($sasurans->pluck('tahun_anggaran')->unique()->sortDesc() as $year)
                    <option value="{{ $year }}">Tahun {{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select id="kepemilikanFilter" class="w-full px-4 py-3 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                <option value="">Semua Kepemilikan</option>
                <option value="lembaga">Lembaga</option>
                <option value="sekretariat">Sekretariat</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0" id="sasaranTable">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5">
                        <th class="w-12 px-6 py-5"></th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Sasaran & Tahun</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kepemilikan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] min-w-[150px]">Indikator</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] min-w-[170px] text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($sasurans as $sasaran)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group" data-kepemilikan="{{ $sasaran->kepemilikan }}">
                        <td class="px-6 py-4 text-center">
                            <button @click="toggleRow({{ $sasaran->id }})" class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                                <svg class="w-4 h-4 transition-transform duration-300" :class="expandedRows.includes({{ $sasaran->id }}) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800 dark:text-white uppercase leading-tight">{{ $sasaran->nama_sasaran }}</span>
                                <span class="text-[11px] text-brand-primary font-black uppercase italic mt-1">T.A {{ $sasaran->tahun_anggaran }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($sasaran->kepemilikan === 'lembaga')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 uppercase">
                                    🏛 Lembaga
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 uppercase">
                                    🗂 Sekretariat
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 uppercase">
                                {{ $sasaran->indikators->count() }} Indikator
                            </span>
                        </td>

                        {{-- Kolom Status: tombol toggle langsung submit form PATCH --}}
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('sasaran.toggleStatus', $sasaran->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="group/toggle inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase transition-all
                                    {{ $sasaran->is_aktif
                                        ? 'bg-green-50 text-green-600 hover:bg-red-50 hover:text-red-500 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-red-500/10 dark:hover:text-red-400'
                                        : 'bg-slate-100 text-slate-400 hover:bg-green-50 hover:text-green-600 dark:bg-white/5 dark:hover:bg-green-500/10 dark:hover:text-green-400' }}">
                                    
                                    {{-- Dot indikator --}}
                                    <span class="w-1.5 h-1.5 rounded-full transition-colors
                                        {{ $sasaran->is_aktif ? 'bg-green-500 animate-pulse group-hover/toggle:bg-red-500' : 'bg-slate-400 group-hover/toggle:bg-green-500' }}">
                                    </span>

                                    {{-- Label: tampil teks status, hover tampil aksi --}}
                                    <span class="group-hover/toggle:hidden">
                                        {{ $sasaran->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <span class="hidden group-hover/toggle:inline">
                                        {{ $sasaran->is_aktif ? 'Nonaktifkan?' : 'Aktifkan?' }}
                                    </span>
                                </button>
                            </form>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="toggleModal(true, {{ $sasaran->setRelation('indikators', $sasaran->indikators)->toJson() }})"
                                        class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form action="{{ route('sasaran.destroy', $sasaran->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus sasaran ini beserta seluruh indikatornya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- Sub Table (Expanded) --}}
                    <tr x-show="expandedRows.includes({{ $sasaran->id }})"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="bg-slate-50/30 dark:bg-white/[0.02]">
                        <td colspan="6" class="px-12 py-4">
                            <div class="overflow-hidden rounded-2xl border border-slate-100 dark:border-white/5 bg-white dark:bg-slate-800/50 shadow-inner">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 dark:bg-white/5">
                                        <tr>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-10">#</th>
                                            <th class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Indikator Kinerja</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                                        @forelse($sasaran->indikators as $i => $ind)
                                        <tr>
                                            <td class="px-4 py-3 text-xs font-black text-slate-400">{{ $i + 1 }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ $ind->nama_indikator }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-4 text-center text-xs text-slate-400 font-bold">Belum ada indikator.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-400 font-bold text-sm">
                            Belum ada data sasaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <template x-teleport="body">
        <div x-show="openModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-cloak>
            <div @click="openModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div class="relative bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[2.5rem] shadow-2xl border border-white/10 overflow-hidden"
                 x-show="openModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="px-8 py-6 border-b dark:border-white/5 flex items-center justify-between bg-slate-50/50 dark:bg-white/5">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight"
                            x-text="editMode ? 'Edit Sasaran' : 'Tambah Sasaran Baru'"></h3>
                        <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Formulir Sasaran & Indikator</p>
                    </div>
                    <button @click="openModal = false" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="editMode ? `{{ url('sasaran') }}/${currentData.id}` : '{{ route('sasaran.store') }}'"
                      method="POST"
                      class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Sasaran</label>
                            <input type="text" name="nama_sasaran" x-model="currentData.nama_sasaran" required
                                class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all"
                                placeholder="Masukkan nama sasaran strategis...">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Kepemilikan</label>
                                <select name="kepemilikan" x-model="currentData.kepemilikan" required
                                    class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                                    <option value="lembaga">🏛 Lembaga</option>
                                    <option value="sekretariat">🗂 Sekretariat</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun Anggaran</label>
                                <input type="number" name="tahun_anggaran" x-model="currentData.tahun_anggaran" required
                                    class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm font-bold outline-none transition-all">
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border-2 transition-colors"
                             :class="currentData.is_aktif ? 'border-green-200 dark:border-green-500/20' : 'border-slate-200 dark:border-white/5'">
                            <div>
                                <p class="text-sm font-black text-slate-700 dark:text-white">Status Sasaran</p>
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5"
                                   x-text="currentData.is_aktif ? 'Sasaran ini sedang aktif digunakan' : 'Sasaran ini tidak aktif'"></p>
                            </div>
                            <button type="button" @click="currentData.is_aktif = !currentData.is_aktif"
                                class="relative inline-flex h-7 w-13 shrink-0 items-center rounded-full transition-colors duration-300 focus:outline-none"
                                :class="currentData.is_aktif ? 'bg-green-500' : 'bg-slate-300 dark:bg-slate-600'">
                                <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform duration-300"
                                      :class="currentData.is_aktif ? 'translate-x-7' : 'translate-x-1'"></span>
                            </button>
                            <input type="hidden" name="is_aktif" :value="currentData.is_aktif ? '1' : '0'">
                        </div>

                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-4 px-1">
                                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Indikator Kinerja</h4>
                                <button type="button" @click="addIndikator()"
                                    class="flex items-center gap-1 text-[10px] font-black text-brand-primary uppercase hover:opacity-70 transition-opacity">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Indikator
                                </button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(ind, index) in currentData.indikators" :key="index">
                                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/5 hover:border-brand-primary/50 transition-all">
                                        <span class="text-[11px] font-black text-slate-300 w-6 text-center shrink-0" x-text="index + 1"></span>
                                        <input type="text"
                                               :name="`indikators[${index}][nama_indikator]`"
                                               x-model="ind.nama_indikator"
                                               placeholder="Nama indikator kinerja..." required
                                               class="flex-1 bg-white dark:bg-slate-800 border-none px-3 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-primary outline-none text-sm font-bold">
                                        <button type="button" @click="removeIndikator(index)"
                                            class="p-2 text-slate-300 hover:text-red-500 transition-colors shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-4 sticky bottom-0 bg-white dark:bg-slate-900 pt-4 border-t dark:border-white/5">
                        <button type="button" @click="openModal = false"
                            class="flex-1 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-[2] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-brand-primary/20 uppercase tracking-widest">
                            Simpan Data Sasaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const yearFilter = document.getElementById('yearFilter');
        const kepemilikanFilter = document.getElementById('kepemilikanFilter');
        const tableBody = document.querySelector('#sasaranTable tbody');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedYear = yearFilter.value;
            const selectedKepemilikan = kepemilikanFilter.value;
            const rows = tableBody.querySelectorAll('tr[data-kepemilikan]');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const year = row.querySelector('.italic')?.innerText.replace('T.A ', '') || '';
                const kepemilikan = row.dataset.kepemilikan || '';
                const subTable = row.nextElementSibling;
                const subText = subTable ? subTable.innerText.toLowerCase() : '';

                const matchesSearch = text.includes(searchTerm) || subText.includes(searchTerm);
                const matchesYear = selectedYear === '' || year === selectedYear;
                const matchesKepemilikan = selectedKepemilikan === '' || kepemilikan === selectedKepemilikan;

                row.style.display = (matchesSearch && matchesYear && matchesKepemilikan) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        yearFilter.addEventListener('change', filterTable);
        kepemilikanFilter.addEventListener('change', filterTable);
    });
</script>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    .w-13 { width: 3.25rem; }
</style>
@endsection