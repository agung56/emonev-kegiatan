@extends('layouts.app')
@section('page_title', 'Ganti Password')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
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
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="px-8 py-7 border-b border-slate-100 dark:border-white/5 bg-slate-50/70 dark:bg-white/5">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Ganti Password</h1>
            <p class="text-sm text-slate-500 font-medium mt-2">Perbarui kata sandi akun Anda dengan memasukkan password lama terlebih dahulu.</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="p-8 space-y-6" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="current_password" class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Password Saat Ini</label>
                <div class="relative">
                    <input id="current_password" :type="showCurrent ? 'text' : 'password'" name="current_password" required
                           class="w-full px-5 pr-12 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 @error('current_password') border-red-400 focus:border-red-500 @else border-transparent focus:border-brand-primary @enderror rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all shadow-inner"
                           placeholder="Masukkan password lama">
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-brand-primary transition-colors">
                        <svg x-show="!showCurrent" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            <circle cx="12" cy="12" r="3" stroke-width="2"></circle>
                        </svg>
                        <svg x-show="showCurrent" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587A2 2 0 0012 14a2 2 0 001.414-.586M9.88 5.09A9.77 9.77 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.721 9.721 0 01-4.152 5.208M6.228 6.228A9.723 9.723 0 002.458 12c1.274 4.057 5.065 7 9.542 7a9.76 9.76 0 004.062-.878"></path>
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label for="password" class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Password Baru</label>
                    <div class="relative">
                        <input id="password" :type="showNew ? 'text' : 'password'" name="password" required
                               class="w-full px-5 pr-12 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 @error('password') border-red-400 focus:border-red-500 @else border-transparent focus:border-brand-primary @enderror rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all shadow-inner"
                               placeholder="Minimal 8 karakter">
                        <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-brand-primary transition-colors">
                            <svg x-show="!showNew" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                <circle cx="12" cy="12" r="3" stroke-width="2"></circle>
                            </svg>
                            <svg x-show="showNew" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587A2 2 0 0012 14a2 2 0 001.414-.586M9.88 5.09A9.77 9.77 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.721 9.721 0 01-4.152 5.208M6.228 6.228A9.723 9.723 0 002.458 12c1.274 4.057 5.065 7 9.542 7a9.76 9.76 0 004.062-.878"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                               class="w-full px-5 pr-12 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all shadow-inner"
                               placeholder="Ulangi password baru">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-brand-primary transition-colors">
                            <svg x-show="!showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                <circle cx="12" cy="12" r="3" stroke-width="2"></circle>
                            </svg>
                            <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587A2 2 0 0012 14a2 2 0 001.414-.586M9.88 5.09A9.77 9.77 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.721 9.721 0 01-4.152 5.208M6.228 6.228A9.723 9.723 0 002.458 12c1.274 4.057 5.065 7 9.542 7a9.76 9.76 0 004.062-.878"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 px-5 py-4">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-300">Tips keamanan: gunakan kombinasi huruf, angka, dan hindari memakai password lama atau informasi yang mudah ditebak.</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                <a href="{{ route('dashboard') }}" class="flex-1 py-4 text-center text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all">Kembali</a>
                <button type="submit" class="flex-[1.5] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-brand-primary/20 uppercase tracking-widest">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
