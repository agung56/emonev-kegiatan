<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark', showPassword: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login | E-Monev Kegiatan KPU Kabupaten Pasuruan</title>
    
    <link rel="icon" type="image/png" href="{{ asset('assets/logo-kpu.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#F59E0B', 
                            primaryHover: '#D97706',
                            black: '#0F172A',
                        }
                    },
                    animation: {
                        'soft-bounce': 'soft-bounce 4s ease-in-out infinite',
                        'spin-slow': 'spin 8s linear infinite',
                    },
                    keyframes: {
                        'soft-bounce': {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-12px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        @media (min-width: 768px) { body { overflow: hidden; height: 100vh; } }
        .yellow-pattern { background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23D97706' fill-opacity='0.06'%3E%3Cpath d='M40 40c0-11.046-8.954-20-20-20S0 28.954 0 40s8.954 20 20 20 20-8.954 20-20zm40 0c0-11.046-8.954-20-20-20S40 28.954 40 40s8.954 20 20 20 20-8.954 20-20z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        .glass-card { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-zinc-950 transition-colors duration-500 min-h-screen flex items-center justify-center p-4 md:p-0 relative">

    <div class="absolute top-6 right-6 z-50">
        <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" 
                class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white shadow-xl flex items-center justify-center hover:scale-110 active:scale-90 transition-all duration-300 border border-slate-200 dark:border-slate-700 group overflow-hidden">
            <div class="relative flex items-center justify-center w-full h-full">
                <svg x-show="!darkMode" x-transition class="w-5 h-5 text-slate-700" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg x-show="darkMode" x-transition class="w-5 h-5 text-brand-primary animate-spin-slow" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707"></path>
                    <circle cx="12" cy="12" r="4"></circle>
                </svg>
            </div>
        </button>
    </div>

    <main class="w-full max-w-5xl bg-white dark:bg-slate-900 md:h-[620px] flex flex-col md:flex-row shadow-[0_30px_100px_-15px_rgba(0,0,0,0.1)] rounded-[2.5rem] overflow-hidden relative z-10 border border-white dark:border-slate-800">
        
        <div class="w-full md:w-5/12 p-10 flex flex-col items-center justify-between bg-white dark:bg-slate-900">
            
            <div class="flex flex-col items-center mt-4 group animate-soft-bounce">
                <div class="flex items-center gap-5 mb-4">
                    <img src="{{ asset('assets/logo-kpu.png') }}" alt="Logo KPU" class="h-16 w-auto drop-shadow-xl">
                    <div class="h-10 w-[2px] bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-700 to-transparent"></div>
                    <img src="{{ asset('assets/logo-1.png') }}" alt="Logo Apps" class="h-14 w-auto drop-shadow-md">
                </div>
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tighter italic">E-MONEV <span class="text-brand-primary">KEGIATAN</span></h1>
                    <p class="text-slate-400 dark:text-slate-500 font-bold text-[10px] uppercase tracking-[0.3em] mt-1">Kabupaten Pasuruan</p>
                </div>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="w-full space-y-5 my-8">
                @csrf
                
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase ml-1">Akses Pengguna</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:text-brand-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>
                        <input type="text" name="login" value="{{ old('login') }}" required 
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800/40 border-2 @error('login') border-red-500 @else border-transparent @enderror focus:border-brand-primary focus:bg-white dark:focus:bg-slate-800 rounded-2xl outline-none transition-all text-sm dark:text-white" 
                               placeholder="NIP / Email">
                    </div>
                    @error('login')
                        <span class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase ml-1">Kata Sandi</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:text-brand-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input :type="showPassword ? 'text' : 'password'" name="password" required 
                               class="w-full pl-12 pr-12 py-3.5 bg-slate-50 dark:bg-slate-800/40 border-2 @error('password') border-red-500 @else border-transparent @enderror focus:border-brand-primary focus:bg-white dark:focus:bg-slate-800 rounded-2xl outline-none transition-all text-sm dark:text-white" 
                               placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 hover:text-brand-primary focus:text-brand-primary transition-colors"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'">
                            <svg x-show="!showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                <circle cx="12" cy="12" r="3" stroke-width="2"></circle>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587A2 2 0 0012 14a2 2 0 001.414-.586M9.88 5.09A9.77 9.77 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.721 9.721 0 01-4.152 5.208M6.228 6.228A9.723 9.723 0 002.458 12c1.274 4.057 5.065 7 9.542 7a9.76 9.76 0 004.062-.878"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] font-bold px-1">
                    <label class="flex items-center text-slate-400 dark:text-slate-500 cursor-pointer hover:text-brand-primary transition-colors">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-brand-primary focus:ring-brand-primary mr-2 transition-all"> Ingat Saya
                    </label>
                </div>

                <button type="submit" class="w-full bg-brand-primary hover:bg-brand-primaryHover text-white font-extrabold py-4 rounded-2xl transition-all shadow-lg shadow-orange-500/10 active:scale-[0.97] text-xs uppercase tracking-widest">
                    Masuk Ke Sistem
                </button>
            </form>

            <div class="w-full text-center">
                <p class="text-[9px] text-slate-300 dark:text-slate-600 font-extrabold uppercase tracking-[0.4em]">
                    &copy; {{ now()->year }} KPU Kabupaten Pasuruan
                </p>
            </div>
        </div>

        <div class="w-full md:w-7/12 bg-brand-primary relative overflow-hidden flex flex-col justify-center p-10 lg:p-16">
            <div class="absolute inset-0 yellow-pattern"></div>
            <div class="relative z-10 text-brand-black">
                <div class="inline-flex items-center gap-2 bg-brand-black text-brand-primary px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-tighter mb-8 shadow-xl">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-primary"></span>
                    </span>
                    Informasi Penyelenggara
                </div>
                
                <h2 class="text-4xl font-extrabold uppercase leading-[0.9] mb-10 tracking-tighter">
                    Visi & Misi<br><span class="text-white drop-shadow-sm">KPU Republik Indonesia</span>
                </h2>

                <div class="space-y-6">
                    <div class="glass-card p-6 rounded-[2rem] shadow-lg">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] mb-3 flex items-center gap-2">
                            <span class="w-6 h-[2px] bg-brand-black"></span> Visi
                        </h3>
                        <p class="text-[13px] font-bold leading-relaxed text-slate-900/90 italic">
                            "Terwujudnya Penyelenggaraan Pemilu dan Pemilihan yang Berkualitas dan Berintegritas sebagai Pilar Demokrasi Substansial dalam rangka Mewujudkan Indonesia Emas 2045."
                        </p>
                    </div>

                    <div class="glass-card p-6 rounded-[2rem] shadow-lg">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                            <span class="w-6 h-[2px] bg-brand-black"></span> Misi Strategis
                        </h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-4 group">
                                <div class="w-6 h-6 rounded-lg bg-brand-black text-white flex shrink-0 items-center justify-center text-[10px] font-bold">1</div>
                                <p class="text-[12px] font-bold leading-snug group-hover:translate-x-1 transition-transform">Menyelenggarakan Pemilu dan Pemilihan yang Memenuhi Asas LUBER dan JURDIL periode 2025-2029.</p>
                            </li>
                            <li class="flex items-start gap-4 group border-t border-brand-black/5 pt-4">
                                <div class="w-6 h-6 rounded-lg bg-brand-black text-white flex shrink-0 items-center justify-center text-[10px] font-bold">2</div>
                                <p class="text-[12px] font-bold leading-snug group-hover:translate-x-1 transition-transform">Menguatkan Kapasitas Kelembagaan KPU yang Efektif, Efisien, dan Akuntabel.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
