@extends('layouts.app')
@section('page_title', 'Manajemen User')

@section('content')
<div class="p-6 space-y-6" x-data="{ 
    openModal: false, 
    editMode: false, 
    currentData: {},
    showPassword: false, // Tambahkan ini
    toggleModal(edit = false, user = {}) {
        this.editMode = edit;
        this.showPassword = false; // Reset mata setiap buka modal
        this.currentData = edit ? { ...user } : { role: 'user', sub_bagian_id: '', is_active: 1 };
        this.openModal = true;
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
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Manajemen User</h1>
            <p class="text-sm text-slate-500 font-medium">Otoritas akses dan penempatan unit kerja pegawai</p>
        </div>
        <button @click="toggleModal(false)" 
                class="flex items-center justify-center gap-2 px-6 py-3 bg-brand-primary text-brand-black text-xs font-black rounded-2xl shadow-lg shadow-brand-primary/20 hover:brightness-110 active:scale-95 transition-all cursor-pointer uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH USER
        </button>
    </div>

    {{-- Search --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 p-4 shadow-sm">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" id="userSearch" onkeyup="searchTable()"
                   class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary/50 rounded-2xl text-sm focus:ring-0 text-slate-700 dark:text-slate-200 transition-all font-semibold placeholder:text-slate-400 shadow-inner" 
                   placeholder="Cari berdasarkan NIP, Nama, atau Email...">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="userTable">
                <thead>
                    <tr class="bg-slate-50 dark:bg-white/5">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Profil Pegawai</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Unit & Otoritas</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] min-w-[170px]">Status Akun</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-brand-primary/10 flex items-center justify-center font-black text-brand-primary uppercase text-xs">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 dark:text-white tracking-tight search-name uppercase">{{ $user->name }}</span>
                                    <span class="text-[11px] text-slate-500 font-medium search-info">{{ $user->nip ?? 'NIP TIDAK ADA' }} • {{ $user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="w-fit px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ $user->role == 'admin' ? 'bg-red-500/10 text-red-600' : 'bg-blue-500/10 text-blue-600' }}">
                                    {{ $user->role }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-400 uppercase italic">
                                    {{ $user->subBagian->nama_sub_bagian ?? 'Non-Bagian' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('users.toggleStatus', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="group/toggle inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase transition-all
                                    {{ $user->is_active
                                        ? 'bg-green-50 text-green-600 hover:bg-red-50 hover:text-red-500 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-red-500/10 dark:hover:text-red-400'
                                        : 'bg-slate-100 text-slate-400 hover:bg-green-50 hover:text-green-600 dark:bg-white/5 dark:hover:bg-green-500/10 dark:hover:text-green-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full transition-colors
                                        {{ $user->is_active ? 'bg-green-500 animate-pulse group-hover/toggle:bg-red-500' : 'bg-slate-400 group-hover/toggle:bg-green-500' }}">
                                    </span>
                                    <span class="group-hover/toggle:hidden">
                                        {{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                    <span class="hidden group-hover/toggle:inline">
                                        {{ $user->is_active ? 'Nonaktifkan?' : 'Aktifkan?' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="toggleModal(true, {{ json_encode($user) }})" 
                                        class="p-2.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                
                                @if($user->id !== 1)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <template x-teleport="body">
        <div x-show="openModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-cloak>
            <div x-show="openModal" x-transition:enter="transition opacity duration-300" @click="openModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="openModal" x-transition:enter="transition duration-300 cubic-bezier(0.34, 1.56, 0.64, 1)"
                 class="relative bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[2.5rem] shadow-2xl border border-white/10 overflow-hidden">
                
                <div class="px-8 py-6 border-b dark:border-white/5 flex items-center justify-between bg-slate-50/50 dark:bg-white/5">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight" x-text="editMode ? 'Edit Profil User' : 'Tambah User Baru'"></h3>
                        <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Sistem Manajemen Otoritas</p>
                    </div>
                    <button @click="openModal = false" class="p-2 text-slate-400 hover:text-red-500 transition-all cursor-pointer">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="editMode ? `{{ url('users') }}/${currentData.id}` : '{{ route('users.store') }}'" method="POST" class="p-8">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        {{-- NIP --}}
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">NIP Pegawai</label>
                            <input type="text" name="nip" x-model="currentData.nip" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                        </div>

                        {{-- Nama --}}
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                            <input type="text" name="name" x-model="currentData.name" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                        </div>

                        {{-- Email --}}
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Alamat Email</label>
                            <input type="email" name="email" x-model="currentData.email" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                        </div>

                        {{-- Otoritas (Select) --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Role / Otoritas</label>
                            <div class="relative group">
                                <select name="role" x-model="currentData.role" class="appearance-none w-full px-5 py-3.5 bg-slate-100 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                                    <option value="user">USER (Pegawai)</option>
                                    <option value="admin">ADMIN (Administrator)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-brand-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Unit Kerja (Select) --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Unit Kerja / Sub Bagian</label>
                            <div class="relative group">
                                <select name="sub_bagian_id" x-model="currentData.sub_bagian_id" class="appearance-none w-full px-5 py-3.5 bg-slate-100 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                                    <option value="">— Tidak Terikat —</option>
                                    @foreach($subBagians as $sb)
                                    <option value="{{ $sb->id }}">{{ $sb->nama_sub_bagian }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-brand-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="space-y-1.5 relative md:col-span-2">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Status Keaktifan Akun</label>
                            <div class="relative group">
                                <select name="is_active" x-model="currentData.is_active" class="appearance-none w-full px-5 py-3.5 bg-slate-100 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                                    <option :value="1">AKTIF (Dapat Akses Sistem)</option>
                                    <option :value="0">NON-AKTIF (Akses Ditangguhkan)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-brand-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">
                                <span x-text="editMode ? 'Ganti Kata Sandi' : 'Kata Sandi Akun'"></span>
                                <template x-if="!editMode">
                                    <span class="text-brand-primary/80 lowercase font-medium italic">(Kosongkan untuk default: 12345678)</span>
                                </template>
                            </label>
                            
                            <div class="relative group">
                                <input :type="showPassword ? 'text' : 'password'" 
                                    name="password" 
                                    class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all shadow-inner placeholder:text-slate-300 dark:placeholder:text-slate-600" 
                                    placeholder="••••••••">
                                
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-brand-primary transition-colors focus:outline-none">
                                    
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.132-5.411m0 0L21 21m-10-10l4 4m-4-4l4-4m0 0a3 3 0 10-4.243 4.243" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="button" @click="openModal = false" class="flex-1 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all cursor-pointer">Batal</button>
                        <button type="submit" class="flex-[2] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-brand-primary/20 uppercase tracking-widest cursor-pointer">Simpan Data Pegawai</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
    function searchTable() {
        const input = document.getElementById("userSearch");
        const filter = input.value.toUpperCase();
        const tr = document.querySelectorAll("#userTable tbody tr");

        tr.forEach(row => {
            const name = row.querySelector(".search-name").textContent.toUpperCase();
            const info = row.querySelector(".search-info").textContent.toUpperCase();
            row.style.display = (name.includes(filter) || info.includes(filter)) ? "" : "none";
        });
    }
</script>
@endsection