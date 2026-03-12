<aside 
    :class="{ 
        'w-72': !sidebarCollapsed, 
        'w-20': sidebarCollapsed, 
        'translate-x-0': sidebarOpen, 
        '-translate-x-full': !sidebarOpen 
    }"
    class="fixed inset-y-0 left-0 z-30 md:relative md:translate-x-0 flex flex-col bg-brand-black text-white transition-all duration-300 ease-in-out h-full border-r border-white/10 shadow-2xl md:shadow-none">
    
    <div class="h-24 flex items-center px-4 bg-brand-black border-b border-white/5 overflow-hidden">
        <div class="flex items-center gap-3 min-w-max">
            
            {{-- Logo Instansi --}}
            <img src="{{ asset('assets/logo-kpu.png') }}" class="h-12 w-auto flex-shrink-0" alt="Logo KPU">

            {{-- Divider antara dua logo --}}
            <div x-show="!sidebarCollapsed" x-transition.opacity.duration.300ms class="h-10 w-[1px] bg-white/20 flex-shrink-0"></div>

            {{-- Logo Aplikasi --}}
            <img x-show="!sidebarCollapsed" x-transition.opacity.duration.300ms 
                src="{{ asset('assets/logo-1.png') }}" 
                class="h-10 w-auto flex-shrink-0" 
                alt="Logo Aplikasi">

            {{-- Divider + Teks App --}}
            <div x-show="!sidebarCollapsed" x-transition.opacity.duration.300ms class="flex items-center gap-3">
                <div class="h-10 w-[1px] bg-white/20"></div>
                <div class="flex flex-col whitespace-nowrap">
                    <span class="font-black text-brand-primary tracking-tighter italic text-xl leading-none">E-MONEV</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.1em] mt-1">
                        Kegiatan
                    </span>
                </div>
            </div>

        </div>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto overflow-x-hidden custom-scrollbar">
        <a href="{{ route('dashboard') }}" 
           class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group"
           title="Dashboard">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Dashboard</span>
        </a>

        @if(auth()->user()->role === 'admin')
            <div class="pt-6 pb-2">
                <div x-show="!sidebarCollapsed" class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] whitespace-nowrap">Master Data</div>
                <div x-show="sidebarCollapsed" class="border-t border-white/10 mx-2 my-2"></div>
            </div>

            <a href="{{ route('sub-bagian.index') }}" 
               class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('sub-bagian.*') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group" 
               title="Sub Bagian">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Sub Bagian</span>
            </a>

            <a href="{{ route('users.index') }}" 
               class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('users.*') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group" 
               title="Manajemen User">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Manajemen User</span>
            </a>

            <a href="{{ route('sasaran.index') }}" 
               class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('sasaran.*') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group" 
               title="Master Sasaran">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Master Sasaran</span>
            </a>
        @endif

        <div class="pt-6 pb-2">
            <div x-show="!sidebarCollapsed" class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] whitespace-nowrap">Menu Utama</div>
            <div x-show="sidebarCollapsed" class="border-t border-white/10 mx-2 my-2"></div>
        </div>

        <a href="{{ route('kegiatans.index') }}" 
           class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('kegiatans.*') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group" 
           title="Data Kegiatan">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Data Kegiatan</span>
        </a>

        <a href="{{ route('pagu.index') }}" 
           class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('pagu.*') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group" 
           title="Master Pagu">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Master Pagu</span>
        </a>

        <a href="{{ route('rekap.index') }}" 
           class="flex items-center p-3 rounded-xl transition-all {{ request()->routeIs('rekap.*') ? 'bg-brand-primary text-brand-black' : 'hover:bg-white/5 text-slate-400 hover:text-white' }} group" 
           title="Laporan Rekap">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span x-show="!sidebarCollapsed" class="ml-4 font-bold text-sm whitespace-nowrap">Rekap Kegiatan</span>
        </a>
    </nav>
</aside>

<div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 md:hidden" 
     x-transition:enter="transition opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak>
</div>
