@extends('layouts.app')
@section('page_title', 'Master Sub Bagian')

@section('content')
<div class="p-6 space-y-5" x-data="{ 
    openModal: false, 
    editMode: false, 
    currentId: null, 
    currentName: '',
    toggleModal(edit = false, id = null, name = '') {
        this.editMode = edit;
        this.currentId = id;
        this.currentName = name;
        this.openModal = true;
        $nextTick(() => { $refs.inputNama.focus() });
    }
}">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Data Sub Bagian</h1>
            <p class="text-xs text-slate-500 font-medium tracking-wide">Manajemen unit kerja internal KPU Kabupaten Pasuruan</p>
        </div>
        <button @click="toggleModal()" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-primary text-brand-black text-[11px] font-black rounded-xl shadow-lg shadow-brand-primary/20 hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH DATA
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 p-3 shadow-sm">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" id="searchInput" onkeyup="filterTable()"
                   class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs focus:ring-2 focus:ring-brand-primary text-slate-700 dark:text-slate-200 transition-all shadow-inner font-semibold placeholder:text-slate-400" 
                   placeholder="Cari sub bagian secara instan...">
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="dataTable">
                <thead>
                    <tr class="bg-slate-50 dark:bg-white/5">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-20 text-center">No</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Sub Bagian</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($data as $index => $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4 text-[12px] font-bold text-slate-500 text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 text-[14px] font-bold text-slate-800 dark:text-white uppercase tracking-tight data-nama">
                            {{ $item->nama_sub_bagian }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="toggleModal(true, '{{ $item->id }}', '{{ $item->nama_sub_bagian }}')" 
                                        class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form action="{{ route('sub-bagian.destroy', $item->id) }}" method="POST" data-confirm="Hapus data ini?" data-confirm-title="Hapus Sub Bagian">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-[12px] text-slate-400 font-medium italic tracking-wider">Belum ada data sub bagian tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="openModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-hidden" x-cloak>
            <div x-show="openModal" x-transition:enter="transition opacity duration-150" @click="openModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="openModal" x-transition:enter="transition duration-300 cubic-bezier(0.34, 1.56, 0.64, 1)"
                 class="relative bg-white dark:bg-slate-900 w-full max-w-lg rounded-[2.5rem] shadow-2xl border border-white/10 overflow-hidden">
                
                <div class="px-8 py-7 border-b dark:border-white/5 flex items-center justify-between bg-slate-50/50 dark:bg-white/5">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight" x-text="editMode ? 'Edit Sub Bagian' : 'Tambah Baru'"></h3>
                        <p class="text-[10px] font-bold text-brand-primary uppercase tracking-[0.2em] mt-1">Sistem Informasi KPU</p>
                    </div>
                    <button @click="openModal = false" class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-full transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="editMode ? `/sub-bagian/${currentId}` : '{{ route('sub-bagian.store') }}'" method="POST" class="p-10 space-y-6">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Nama Unit Kerja</label>
                        <input type="text" name="nama_sub_bagian" x-model="currentName" x-ref="inputNama" required
                               class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all shadow-inner focus:shadow-brand-primary/5"
                               placeholder="Contoh: Teknis Penyelenggaraan">
                    </div>

                    <div class="pt-4 flex gap-4">
                        <button type="button" @click="openModal = false" 
                                class="flex-1 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-[2] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-105 active:scale-95 transition-all shadow-xl shadow-brand-primary/25 uppercase tracking-widest">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toUpperCase();
        const tr = document.querySelectorAll("#dataTable tbody tr");

        tr.forEach(row => {
            const td = row.querySelector(".data-nama");
            if (td) {
                const text = td.textContent || td.innerText;
                row.style.display = text.toUpperCase().includes(filter) ? "" : "none";
            }
        });
    }
</script>
@endsection
