<header class="h-20 min-h-[5rem] flex items-center justify-between px-4 md:px-6 bg-white dark:bg-slate-900 border-b dark:border-white/5 shadow-sm transition-all sticky top-0 z-20">
    
    <div class="flex items-center gap-3 md:gap-4 flex-shrink-0">
        <button @click="if (window.innerWidth >= 768) { sidebarCollapsed = !sidebarCollapsed } else { sidebarOpen = !sidebarOpen }" 
                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-brand-primary hover:text-brand-black transition-all group">
            <svg class="w-6 h-6 transition-transform duration-300" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
        </button>

        <h2 class="font-extrabold text-slate-800 dark:text-white tracking-tight uppercase text-[11px] md:text-sm whitespace-nowrap">
            Halaman <span class="text-brand-primary">@yield('page_title', 'Dashboard')</span>
        </h2>
    </div>

    <div class="flex items-center gap-2 md:gap-4">
        <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" 
                class="p-2 md:p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-brand-primary transition-all flex-shrink-0">
            <svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
            <svg x-show="darkMode" x-cloak class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4" stroke-width="2"></circle>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2m10-10h-2M4 12H2m17.071-7.071l-1.414 1.414M6.343 17.657l-1.414 1.414m14.142 0l-1.414-1.414M6.343 6.343L4.929 4.929"></path>
            </svg>
        </button>

        <div class="h-8 w-[1px] bg-slate-200 dark:bg-white/10 hidden sm:block"></div>

        <div class="flex items-center gap-3 relative min-w-0" x-data="{ open: false }" @click.away="open = false">
            <div class="text-right hidden sm:block min-w-0 max-w-[140px] md:max-w-[220px] lg:max-w-[320px]">
                <p class="text-[10px] md:text-xs font-black text-slate-800 dark:text-white leading-none uppercase truncate">{{ auth()->user()->name }}</p>
                <p class="text-[9px] md:text-[10px] font-bold text-brand-primary leading-none mt-1 uppercase">{{ auth()->user()->role }}</p>
            </div>
            
            <button @click="open = !open" class="h-9 w-9 md:h-10 md:w-10 rounded-xl bg-brand-primary flex items-center justify-center font-black text-brand-black shadow-lg shadow-brand-primary/20 hover:scale-105 transition-transform flex-shrink-0">
                {{ substr(auth()->user()->name, 0, 1) }}
            </button>

            <div x-show="open" x-cloak x-transition
                 class="absolute right-0 top-14 w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-white/5 py-2 z-50">
                <div class="px-4 py-2 border-b border-slate-100 dark:border-white/5 sm:hidden">
                    <p class="text-[10px] font-black text-slate-800 dark:text-white uppercase truncate">{{ auth()->user()->name }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 text-[10px] md:text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        KELUAR SISTEM
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
