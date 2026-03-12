<!DOCTYPE html>
<html lang="id" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark', 
    sidebarOpen: false, 
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' 
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Monev KPU Kabupaten Pasuruan</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-white transition-colors duration-300">
    
    <div class="flex h-screen overflow-hidden">
        @include('layouts.sidebar')

        <div class="flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            @include('layouts.header')

            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
         class="fixed inset-0 z-20 bg-black/50 md:hidden transition-opacity"></div>
</body>
</html>
