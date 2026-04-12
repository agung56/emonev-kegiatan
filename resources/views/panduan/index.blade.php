<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Panduan | E-Monev Kegiatan KPU Kabupaten Pasuruan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo-kpu.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: { primary: '#F59E0B', dark: '#B45309' } } } }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .pdf-page {
            background: #ffffff; width: 794px; min-height: 1123px; margin: 0 auto 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12); display: flex; flex-direction: column;
            position: relative; border-radius: 2px; overflow: hidden;
        }
        .dark .pdf-page { background: #1e2433; box-shadow: 0 4px 24px rgba(0,0,0,0.4); }
        .pdf-page-header { background: #F59E0B; padding: 10px 40px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .pdf-page-header span { font-size: 11px; font-weight: 800; color: #1c1917; text-transform: uppercase; letter-spacing: .04em; }
        .pdf-page-body { flex: 1; padding: 40px 48px 32px; }
        .pdf-page-footer { padding: 10px 40px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .dark .pdf-page-footer { border-top-color: #334155; }
        .pdf-page-footer span { font-size: 11px; color: #94a3b8; font-weight: 500; }
        .pdf-cover .pdf-page-header { background: #0F172A; }
        .pdf-cover .pdf-page-header span { color: #F59E0B; }
        .pdf-cover .pdf-page-body { background: linear-gradient(135deg, #0F172A 0%, #1e293b 60%, #0f172a 100%); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; border-bottom: none; }
        .pdf-cover .pdf-page-footer { background: #0F172A; border-top: none; }
        .pdf-cover .pdf-page-footer span { color: #475569; }
        
        .doc-chapter-label { font-size: 11px; font-weight: 900; letter-spacing: 0.25em; text-transform: uppercase; color: #F59E0B; margin-bottom: 8px; }
        .doc-chapter-title { font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 22px 0; line-height: 1.15; }
        .dark .doc-chapter-title { color: #f1f5f9; }
        .doc-section-title { font-size: 16px; font-weight: 800; color: #1e293b; margin: 22px 0 10px 0; line-height: 1.35; }
        .dark .doc-section-title { color: #e2e8f0; }
        .doc-subsection-title { font-size: 14px; font-weight: 700; color: #334155; margin: 18px 0 8px 0; line-height: 1.35; }
        .dark .doc-subsection-title { color: #cbd5e1; }
        .doc-p { font-size: 13.5px; line-height: 1.8; color: #374151; margin: 0 0 14px 0; }
        .dark .doc-p { color: #94a3b8; }
        .doc-ul { list-style: none; padding: 0; margin: 0 0 12px 0; }
        .doc-ul li { display: flex; gap: 8px; font-size: 13.5px; line-height: 1.75; color: #374151; margin-bottom: 8px; }
        .dark .doc-ul li { color: #94a3b8; }
        .doc-ul li::before { content: '•'; color: #F59E0B; font-weight: 900; }
        .doc-table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-bottom: 18px; }
        .doc-table thead tr { background: #F59E0B; }
        .doc-table thead th { padding: 9px 12px; text-align: left; font-size: 10.5px; font-weight: 900; text-transform: uppercase; color: #1c1917; letter-spacing: .04em; }
        .doc-table tbody td { padding: 11px 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; line-height: 1.65; }
        .dark .doc-table tbody td { border-bottom-color: #334155; }
        .doc-step { display: flex; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .dark .doc-step { border-bottom-color: #1e293b; }
        .doc-step-num { width: 30px; height: 30px; background: #F59E0B; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #1c1917; flex-shrink: 0; }
        .doc-step-title { font-size: 13px; font-weight: 800; color: #1e293b; line-height: 1.35; }
        .dark .doc-step-title { color: #e2e8f0; }
        .doc-step-desc { font-size: 12.5px; color: #6b7280; line-height: 1.7; }
        .doc-callout { border-left: 3px solid #F59E0B; background: #fffbeb; padding: 10px 14px; margin-bottom: 14px; border-radius: 0 6px 6px 0; }
        .dark .doc-callout { background: rgba(245,158,11,0.08); }
        .mono { font-family: 'JetBrains Mono', monospace; background: #f1f5f9; color: #b45309; font-size: 11.5px; padding: 1px 5px; border-radius: 4px; }
        .dark .mono { background: #1e293b; color: #fbbf24; }
        .doc-image { width: 100%; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .dark .doc-image { border-color: #334155; }
        .doc-image-caption { font-size: 11px; color: #94a3b8; text-align: center; margin-top: -8px; margin-bottom: 16px; font-style: italic; }
        @media (max-width: 860px) {
            .pdf-page { width: calc(100vw - 24px); min-height: auto; }
            .pdf-page-header span, .pdf-page-footer span { font-size: 10px; }
            .doc-chapter-label { font-size: 10px; }
            .doc-chapter-title { font-size: 22px; }
            .doc-section-title { font-size: 15px; }
            .doc-subsection-title { font-size: 13px; }
            .doc-p, .doc-ul li { font-size: 12.5px; line-height: 1.75; }
            .doc-table { font-size: 11.5px; }
            .doc-table thead th { font-size: 10px; }
            .doc-step-title { font-size: 12px; }
            .doc-step-desc { font-size: 11.5px; }
        }
        @media print { .no-print { display: none !important; } .pdf-page { box-shadow: none; margin-bottom: 0; page-break-after: always; } }
    </style>
</head>
<body class="bg-slate-200 dark:bg-zinc-950 transition-colors duration-300">
@php
    $panduanPdfDownload = route('panduan.pdf.download');
    $pHeader = 'E-MONEV Kegiatan | KPU Kabupaten Pasuruan';
@endphp

{{-- TOOLBAR --}}
<div class="no-print fixed top-0 left-0 right-0 z-50 flex h-12 items-center justify-between bg-white/95 backdrop-blur-sm shadow-sm px-4 dark:bg-zinc-900/95 sm:px-6">
    <div class="flex items-center gap-3">
        <img src="{{ asset('assets/logo-kpu.png') }}" alt="KPU" class="h-6 w-6">
        <span class="text-sm font-bold dark:text-white">Buku Panduan E-MONEV Kegiatan</span>
    </div>
    <div class="flex items-center gap-2">
        @if(auth()->check())
            <a href="{{route('dashboard')}}" class="inline-flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-[0.16em] text-slate-700 hover:border-brand-primary hover:bg-white hover:text-brand-primary dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-200 dark:hover:bg-zinc-800 transition-all active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
        @else
            <a href="{{route('login')}}" class="inline-flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-[0.16em] text-slate-700 hover:border-brand-primary hover:bg-white hover:text-brand-primary dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-200 dark:hover:bg-zinc-800 transition-all active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline">Login</span>
            </a>
        @endif
        <button @click="darkMode=!darkMode; localStorage.setItem('theme',darkMode?'dark':'light')" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
            <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg x-show="darkMode" class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.07-7.07-1.41 1.41M6.34 17.66l-1.41 1.41m14.14 0-1.41-1.41M6.34 6.34 4.93 4.93"/></svg>
        </button>
        <a href="{{$panduanPdfDownload}}" class="inline-flex h-8 items-center rounded-lg bg-brand-primary hover:bg-brand-dark text-slate-900 text-[10px] font-black uppercase tracking-[0.16em] px-4 shadow-sm transition-all active:scale-95">Unduh PDF</a>
    </div>
</div>

<div class="pages-wrapper pt-16 pb-12 px-4">
    {{-- PAGE 1 --}}
    <div class="pdf-page pdf-cover">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Buku Panduan Pengguna</span></div>
        <div class="pdf-page-body">
            <img src="{{asset('assets/logo-kpu.png')}}" alt="KPU" class="w-24 h-24 mb-6">
            <h1 class="text-4xl font-extrabold text-white leading-tight mb-2 uppercase">Buku Panduan Pengguna</h1>
            <p class="text-2xl font-black text-brand-primary tracking-widest mb-1 uppercase">E-MONEV Kegiatan</p>
            <p class="text-sm font-bold text-slate-400">KPU KABUPATEN PASURUAN</p>
            <p class="text-xs text-slate-500 mt-10">v1.0 / Tahun 2026</p>
        </div>
        <div class="pdf-page-footer"><span>Elektronik Monitoring dan Evaluasi Kegiatan</span><span>Halaman 1 dari 19</span></div>
    </div>

    {{-- PAGE 2 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 1 — Pendahuluan</span></div>
        <div class="pdf-page-body">
            <div class="doc-chapter-label">Bab 1</div><h2 class="doc-chapter-title">Pendahuluan</h2>
            <h3 class="doc-section-title">1.1 Latar Belakang</h3>
            <p class="doc-p">Sistem E-MONEV Kegiatan adalah aplikasi berbasis web yang dikembangkan oleh KPU Kabupaten Pasuruan untuk mendukung proses monitoring dan evaluasi kegiatan secara digital, transparan, dan akuntabel.</p>
            <p class="doc-p">Melalui sistem ini, setiap pengguna dapat memantau kegiatan yang sudah dilaksanakan secara real-time melalui tampilan dashboard yang informatif dan mudah dipahami.</p>
            <h3 class="doc-section-title">1.2 Tujuan Sistem</h3>
            <ul class="doc-ul">
                <li>Meningkatkan efisiensi pemantauan kegiatan KPU Kabupaten Pasuruan.</li>
                <li>Menyajikan data real-time progres kegiatan dan realisasi anggaran dalam satu tampilan.</li>
                <li>Mendukung tata kelola pemerintahan yang transparan dan akuntabel.</li>
                <li>Menjadi alat bantu pengambilan keputusan berbasis data bagi pimpinan.</li>
            </ul>
            <h3 class="doc-section-title">1.3 Visi & Misi KPU Republik Indonesia</h3>
            <div class="border rounded-lg overflow-hidden dark:border-slate-700">
                <table class="doc-table mb-0">
                    <tbody>
                        <tr><td class="bg-slate-50 dark:bg-slate-800/50 font-bold w-20">VISI</td><td>Terwujudnya Penyelenggaraan Pemilu dan Pemilihan yang Berkualitas dan Berintegritas sebagai Pilar Demokrasi Substansial dalam rangka Mewujudkan Indonesia Emas 2045.</td></tr>
                        <tr><td class="bg-slate-50 dark:bg-slate-800/50 font-bold">MISI 1</td><td>Menyelenggarakan Pemilu dan Pemilihan yang Memenuhi Asas LUBER dan JURDIL periode 2025-2029.</td></tr>
                        <tr><td class="bg-slate-50 dark:bg-slate-800/50 font-bold">MISI 2</td><td>Menguatkan Kapasitas Kelembagaan KPU yang Efektif, Efisien, dan Akuntabel.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pdf-page-footer"><span>Halaman 2 dari 19</span></div>
    </div>

    {{-- PAGE 3 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 1 — Pendahuluan</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">1.4 Pengguna Sistem</h3>
            <p class="doc-p">Sistem E-MONEV Kegiatan hanya dapat diakses oleh dua jenis akun resmi:</p>
            <table class="doc-table">
                <thead><tr><th class="w-1/4">Jenis Akun</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td class="font-bold">Super Admin</td><td>Pengelola sistem secara penuh — mengelola data kegiatan, pengguna, dan seluruh konfigurasi sistem.</td></tr>
                    <tr><td class="font-bold">Subbag</td><td>Operator bagian — dapat melihat, menambah, dan mengedit seluruh data kegiatan termasuk kegiatan Subbag lain.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pdf-page-footer"><span>Halaman 3 dari 19</span></div>
    </div>

    {{-- PAGE 4 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 2 — Akses Sistem</span></div>
        <div class="pdf-page-body">
            <div class="doc-chapter-label">Bab 2</div><h2 class="doc-chapter-title">Akses Sistem</h2>
            <h3 class="doc-section-title">2.1 Alamat Akses</h3>
            <p class="doc-p">Sistem dapat diakses melalui browser dengan mengetikkan alamat URL berikut:</p>
            <div class="bg-slate-900 rounded-lg p-3 mb-4 text-center"><a href="https://emonev.kpupasuruan.web.id" class="text-brand-primary font-mono font-bold">https://emonev.kpupasuruan.web.id</a></div>
            <h3 class="doc-section-title">2.2 Informasi Akun Login</h3>
            <p class="doc-p">Setiap pengguna akan mendapatkan akun dari Super Admin. Berikut format kredensial yang digunakan:</p>
            <table class="doc-table">
                <thead><tr><th class="w-1/3">Field</th><th>Keterangan & Format</th></tr></thead>
                <tbody>
                    <tr><td class="font-bold">Akses Pengguna</td><td>Gunakan salah satu dari dua format berikut:<br>• <strong>Format email:</strong> nama_subbag@kpu.web.id<br>• <strong>Format NIP:</strong> NIP masing-masing Kasubbag</td></tr>
                    <tr><td class="font-bold">Kata Sandi</td><td>Password default yang diberikan oleh Super Admin:<br>Default password: <code class="mono">rahasia</code><br>Segera ganti password setelah login pertama kali.</td></tr>
                </tbody>
            </table>
            <div class="doc-callout bg-amber-50 border-amber-500 dark:bg-amber-900/10 dark:border-amber-700">
                <div class="callout-title">⚠ Keamanan Akun</div>
                <ul class="doc-ul mb-0">
                    <li>Segera ganti kata sandi default ‘rahasia’ setelah login pertama kali.</li>
                    <li>Jangan bagikan kata sandi kepada siapapun, termasuk rekan kerja.</li>
                    <li>Hubungi Super Admin jika akun belum diterima atau lupa kata sandi.</li>
                </ul>
            </div>
        </div>
        <div class="pdf-page-footer"><span>Halaman 4 dari 19</span></div>
    </div>

    {{-- PAGE 5 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 2 — Langkah Login</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">2.3 Langkah Login</h3>
            <div class="border rounded-lg p-2 mb-4 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Buka Browser</div>Jalankan browser pilihan Anda (Chrome, Firefox, Edge, atau Safari).</div></div>
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Masukkan URL</div>Ketik https://emonev.kpupasuruan.web.id lalu tekan Enter.</div></div>
                <div class="doc-step"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Isi Akses Pengguna</div>Masukkan email (nama_subbag@kpu.web.id) atau NIP Kasubbag Anda.</div></div>
                <div class="doc-step"><div class="doc-step-num">4</div><div class="doc-step-desc"><div class="doc-step-title">Isi Kata Sandi</div>Masukkan kata sandi. Default: rahasia. Gunakan ikon mata untuk menampilkan karakter.</div></div>
                <div class="doc-step"><div class="doc-step-num">5</div><div class="doc-step-desc"><div class="doc-step-title">Ingat Saya</div>Centang jika ingin tetap login di perangkat yang sama (opsional).</div></div>
                <div class="doc-step"><div class="doc-step-num">6</div><div class="doc-step-desc"><div class="doc-step-title">Masuk Ke Sistem</div>Klik tombol oranye ‘MASUK KE SISTEM’.</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">7</div><div class="doc-step-desc"><div class="doc-step-title">Ganti Password</div>Jika ini login pertama, segera ganti kata sandi melalui menu profil.</div></div>
            </div>
            <img src="{{ asset('assets/panduan-images/page05_img01.jpg') }}" class="doc-image">
            <p class="doc-image-caption">Gambar: Halaman Login Sistem E-MONEV Kegiatan</p>
        </div>
        <div class="pdf-page-footer"><span>Halaman 5 dari 19</span></div>
    </div>

    {{-- PAGE 6 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 2 — Keluar</span></div>
        <div class="pdf-page-body">
            <div class="doc-callout mb-6">
                <p class="doc-p">Catatan Penting:<br>• Pastikan tidak ada spasi di awal/akhir isian username maupun password.<br>• Gunakan ikon mata pada kolom Kata Sandi untuk memastikan isian sudah benar.<br>• Hubungi Super Admin jika login gagal setelah beberapa percobaan.<br>• Selalu logout setelah selesai, terutama pada perangkat bersama.</p>
            </div>
            <h3 class="doc-section-title">2.5 Logout</h3>
            <p class="doc-p">Klik menu profil atau ikon logout di sudut kanan atas halaman, kemudian konfirmasi untuk keluar dari sistem.</p>
            <img src="{{ asset('assets/panduan-images/page06_img02.png') }}" class="doc-image">
        </div>
        <div class="pdf-page-footer"><span>Halaman 6 dari 19</span></div>
    </div>

    {{-- PAGE 7 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 3 — Navigasi</span></div>
        <div class="pdf-page-body">
            <div class="doc-chapter-label">Bab 3</div><h2 class="doc-chapter-title">Tampilan & Navigasi Sistem</h2>
            <p class="doc-p">Setelah login, pengguna akan melihat antarmuka utama sistem E-MONEV yang terdiri dari sidebar navigasi di sisi kiri dan area konten di sisi kanan.</p>
            <h3 class="doc-section-title">3.1 Sidebar Navigasi</h3>
            <table class="doc-table">
                <thead><tr><th class="w-1/3">Kelompok Menu</th><th>Isi Menu</th></tr></thead>
                <tbody>
                    <tr><td>(Atas)</td><td>Dashboard — halaman ringkasan utama.</td></tr>
                    <tr><td>MASTER DATA</td><td>Sub Bagian — data sub bagian; Manajemen User — kelola akun pengguna.</td></tr>
                    <tr><td>MENU UTAMA</td><td>Data Kegiatan, Master Pagu, Rekap Kegiatan, Data Sasaran.</td></tr>
                </tbody>
            </table>
            <h3 class="doc-section-title">3.2 Header Aplikasi</h3>
            <ul class="doc-ul">
                <li>Pojok kiri atas: logo E-MONEV KEGIATAN dengan ikon grafik oranye.</li>
                <li>Pojok kanan atas: nama pengguna yang sedang login beserta perannya (contoh: AGUNG MULYONO — ADMIN).</li>
                <li>Ikon bulan (☽): tombol toggle mode gelap/terang layar.</li>
            </ul>
            <img src="{{ asset('assets/panduan-images/page07_img03.jpg') }}" class="doc-image mt-4">
        </div>
        <div class="pdf-page-footer"><span>Halaman 7 dari 19</span></div>
    </div>

    {{-- PAGE 8 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 3 — Dashboard</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">3.3 Halaman Login</h3>
            <ul class="doc-ul">
                <li>Panel kiri (putih): form login dengan kolom Akses Pengguna (NIP/Email), Kata Sandi, opsi Ingat Saya, dan tombol oranye MASUK KE SISTEM.</li>
                <li>Panel kanan (oranye): menampilkan Informasi Penyelenggara berisi Visi & Misi KPU Republik Indonesia.</li>
            </ul>
            <h3 class="doc-section-title">3.4 Dashboard Utama</h3>
            <ul class="doc-ul">
                <li>Ringkasan status kegiatan: jumlah kegiatan direncanakan, sedang berjalan, dan selesai.</li>
                <li>Grafik progres realisasi kegiatan per periode.</li>
                <li>Daftar kegiatan terbaru dan perkembangannya.</li>
                <li>Indikator capaian per bidang/subbagian.</li>
            </ul>
            <img src="{{ asset('assets/panduan-images/page08_img04.png') }}" class="doc-image">
            <div class="doc-chapter-label mt-8">Bab 4</div><h2 class="doc-chapter-title">Rekap Kegiatan</h2>
            <p class="doc-p">Halaman Rekap Kegiatan menampilkan realisasi anggaran kegiatan KPU Kabupaten Pasuruan secara menyeluruh. Dapat diakses melalui menu ‘Rekap Kegiatan’ pada sidebar MENU UTAMA.</p>
        </div>
        <div class="pdf-page-footer"><span>Halaman 8 dari 19</span></div>
    </div>

    {{-- PAGE 9 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 4 — Rekap</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">4.1 Cara Membuka</h3>
            <div class="border rounded-lg p-2 mb-6 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Login</div>Masuk ke sistem menggunakan akun Super Admin atau Subbag.</div></div>
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Buka Sidebar</div>Klik menu ‘Rekap Kegiatan’ pada kelompok MENU UTAMA di sidebar kiri.</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Halaman Terbuka</div>Halaman Rekap Kegiatan tampil dengan filter di bagian atas.</div></div>
            </div>
            <h3 class="doc-section-title">4.2 Filter Tampilan Data</h3>
            <table class="doc-table">
                <thead><tr><th class="w-1/4">Filter</th><th class="w-1/4">Pilihan</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td>TAHUN</td><td>Angka tahun</td><td>Pilih tahun anggaran yang ingin ditampilkan.</td></tr>
                    <tr><td>PERIODE</td><td>Bulanan / Triwulan / Tahunan</td><td>Pilih jenis periode pelaporan.</td></tr>
                    <tr><td>BULAN</td><td>Januari s.d. Desember</td><td>Aktif saat periode Bulanan dipilih.</td></tr>
                </tbody>
            </table>
            <p class="doc-p text-sm">Setelah memilih filter, klik tombol <strong>‘TAMPILKAN’</strong> (oranye) untuk memuat data.</p>
            <img src="{{ asset('assets/panduan-images/page09_img05.png') }}" class="doc-image mt-4">
        </div>
        <div class="pdf-page-footer"><span>Halaman 9 dari 19</span></div>
    </div>

    {{-- PAGE 10 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 4 — Struktur Hasil</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">4.3 Struktur Tampilan Hasil</h3>
            <h4 class="doc-subsection-title">4.3.1 Header Sasaran</h4>
            <table class="doc-table">
                <thead><tr><th class="w-1/3">Elemen</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td>Label SASARAN</td><td>Nama kelompok/unit kerja (contoh: SASARAN - SEKRETARIAT).</td></tr>
                    <tr><td>Judul Sasaran</td><td>Deskripsi sasaran strategis kegiatan.</td></tr>
                    <tr><td>Label Periode</td><td>Periode data (oranye di pojok kanan, contoh: APRIL 2026).</td></tr>
                </tbody>
            </table>
            <h4 class="doc-subsection-title">4.3.2 Baris Indikator & Anggaran</h4>
            <table class="doc-table text-center">
                <thead><tr><th>Kolom</th><th>Pagu</th><th>Realisasi</th><th>Sisa</th></tr></thead>
                <tbody>
                    <tr><td class="bg-slate-50 dark:bg-slate-800/50">Keterangan</td><td>Total anggaran dialokasikan</td><td>Jumlah sudah terpakai</td><td>Sisa belum terpakai</td></tr>
                    <tr><td class="bg-slate-50 dark:bg-slate-800/50">Warna</td><td>Hitam normal</td><td class="text-brand-primary font-bold">Oranye</td><td>Hitam normal</td></tr>
                </tbody>
            </table>
            <div class="doc-callout mt-4">
                <ul class="doc-ul mb-0">
                    <li><strong>Hijau (badge):</strong> Realisasi tinggi — kegiatan berjalan baik (contoh: 94.85%).</li>
                    <li><strong>Merah (badge):</strong> Realisasi rendah — perlu perhatian.</li>
                </ul>
            </div>
            <h4 class="doc-subsection-title">4.3.3 Tabel Detail Kegiatan</h4>
            <table class="doc-table">
                <thead><tr><th class="w-1/4">Kolom</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td>KEGIATAN</td><td>Nama kegiatan beserta label unit (oranye).</td></tr>
                    <tr><td>PROGRAM</td><td>Nama program terkait dan tahun anggaran.</td></tr>
                    <tr><td>LOKUS</td><td>Lokasi pelaksanaan kegiatan.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pdf-page-footer"><span>Halaman 10 dari 19</span></div>
    </div>

    {{-- PAGE 11 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 4 — Struktur Hasil</span></div>
        <div class="pdf-page-body">
            <table class="doc-table">
                <tbody>
                    <tr><td class="w-1/4">DETAIL ANGGARAN</td><td>Rincian akun belanja yang digunakan.</td></tr>
                    <tr><td>TANGGAL</td><td>Rentang tanggal pelaksanaan.</td></tr>
                    <tr><td>REALISASI</td><td>Total realisasi anggaran dalam rupiah (tebal).</td></tr>
                </tbody>
            </table>
            <h3 class="doc-section-title">4.4 Catatan</h3>
            <ul class="doc-ul">
                <li>Halaman ini hanya menampilkan data — tidak ada fitur ekspor atau cetak laporan.</li>
                <li>Pastikan filter sudah sesuai sebelum klik TAMPILKAN.</li>
                <li>Jika data tidak muncul, periksa apakah data sudah diinput pada periode tersebut.</li>
            </ul>
        </div>
        <div class="pdf-page-footer"><span>Halaman 11 dari 19</span></div>
    </div>

    {{-- PAGE 12 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 5 — Panduan Peran</span></div>
        <div class="pdf-page-body">
            <div class="doc-chapter-label">Bab 5</div><h2 class="doc-chapter-title">Panduan Per Peran Pengguna</h2>
            <h3 class="doc-section-title">5.1 Super Admin</h3>
            <p class="doc-p">Super Admin memiliki hak akses penuh terhadap seluruh fitur dan data dalam sistem E-MONEV Kegiatan.</p>
            <h4 class="doc-subsection-title">5.1.1 Mengelola Data Kegiatan</h4>
            <div class="border rounded-lg p-2 mb-6 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Buka Menu</div>Pilih menu ‘Data Kegiatan’ pada kelompok MENU UTAMA di sidebar.</div></div>
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Tambah Kegiatan</div>Klik tombol ‘Tambah Kegiatan’ untuk menambahkan data kegiatan baru.</div></div>
                <div class="doc-step"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Isi Formulir</div>Lengkapi: nama kegiatan, tanggal, lokasi, anggaran, subbag penanggung jawab.</div></div>
                <div class="doc-step"><div class="doc-step-num">4</div><div class="doc-step-desc"><div class="doc-step-title">Edit Kegiatan</div>Klik ikon Edit pada baris kegiatan yang ingin diperbarui.</div></div>
                <div class="doc-step"><div class="doc-step-num">5</div><div class="doc-step-desc"><div class="doc-step-title">Hapus Kegiatan</div>Klik ikon Hapus pada kegiatan yang ingin dihapus dari sistem.</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">6</div><div class="doc-step-desc"><div class="doc-step-title">Simpan</div>Klik ‘Simpan’ untuk menyimpan data.</div></div>
            </div>
            <img src="{{ asset('assets/panduan-images/page12_img06.jpg') }}" class="doc-image">
        </div>
        <div class="pdf-page-footer"><span>Halaman 12 dari 19</span></div>
    </div>

    {{-- PAGE 13 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 5 — Manajemen User</span></div>
        <div class="pdf-page-body">
            <h4 class="doc-subsection-title">5.1.2 Mengelola Akun Pengguna</h4>
            <div class="border rounded-lg p-2 mb-6 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Buka Menu User</div>Pilih menu ‘Manajemen User’ pada kelompok MASTER DATA.</div></div>
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Tambah Akun</div>Klik ‘Tambah Pengguna’, isi nama, NIP/email, password, dan pilih peran Subbag.</div></div>
                <div class="doc-step"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Edit / Nonaktifkan</div>Gunakan tombol Edit atau Nonaktifkan pada akun yang dipilih.</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">4</div><div class="doc-step-desc"><div class="doc-step-title">Reset Password</div>Gunakan opsi ‘Reset Password’ jika pengguna Subbag lupa kata sandi.</div></div>
            </div>
            <img src="{{ asset('assets/panduan-images/page13_img07.png') }}" class="doc-image mb-10">
            <h3 class="doc-section-title">5.2 Subbag (Operator)</h3>
            <p class="doc-p">Akun Subbag dapat melihat, menambah, dan mengedit seluruh data kegiatan dalam sistem — termasuk kegiatan yang diinput oleh Subbag lain. Hanya penghapusan data yang merupakan kewenangan eksklusif Super Admin.</p>
            <h4 class="doc-subsection-title">5.2.1 Menambah Kegiatan Baru</h4>
            <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Login</div>Masuk dengan akun Subbag (email atau NIP, password: rahasia).</div></div>
        </div>
        <div class="pdf-page-footer"><span>Halaman 13 dari 19</span></div>
    </div>

    {{-- PAGE 14 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 5 — Subbag Kegiatan</span></div>
        <div class="pdf-page-body">
            <div class="border rounded-lg p-2 mb-6 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Buka Menu</div>Pilih menu ‘Data Kegiatan’ pada kelompok MENU UTAMA di sidebar.</div></div>
                <div class="doc-step"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Tambah Kegiatan</div>Klik tombol ‘Tambah Kegiatan’ yang tersedia di halaman.</div></div>
                <div class="doc-step"><div class="doc-step-num">4</div><div class="doc-step-desc"><div class="doc-step-title">Isi Formulir</div>Lengkapi data kegiatan: nama, tanggal, program, lokus, detail anggaran.</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">5</div><div class="doc-step-desc"><div class="doc-step-title">Simpan</div>Klik ‘Simpan’ untuk menyimpan kegiatan baru.</div></div>
            </div>
            <h4 class="doc-subsection-title">5.2.2 Melihat & Mengedit Kegiatan (Termasuk Subbag Lain)</h4>
            <p class="doc-p">Subbag dapat melihat dan mengedit data kegiatan milik Subbag manapun, tidak terbatas pada bidang sendiri.</p>
            <div class="border rounded-lg p-2 mb-6 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Buka Daftar</div>Buka menu ‘Data Kegiatan’. Seluruh kegiatan dari semua Subbag akan tampil.</div></div>
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Pilih Kegiatan</div>Temukan kegiatan yang ingin dilihat atau diedit (dapat dari Subbag lain).</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Klik Detail/Edit</div>Klik nama kegiatan untuk melihat detail, atau klik ikon Edit untuk mengedit.</div></div>
            </div>
            <img src="{{ asset('assets/panduan-images/page14_img08.jpg') }}" class="doc-image">
        </div>
        <div class="pdf-page-footer"><span>Halaman 14 dari 19</span></div>
    </div>

    {{-- PAGE 15 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 5 — Dokumentasi</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">5.2.3 Fitur Dokumentasi & Download ZIP</h3>
            <p class="doc-p">Setiap kegiatan dilengkapi panel Dokumentasi yang menampilkan daftar file pendukung seperti foto dan dokumen bukti pelaksanaan kegiatan.</p>
            <table class="doc-table">
                <thead><tr><th class="w-1/3">Elemen</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td>Panel Dokumentasi</td><td>Menampilkan jumlah file yang tersedia (contoh: 11 file) beserta nama, jenis (IMAGE/PDF), dan ukuran masing-masing file.</td></tr>
                </tbody>
            </table>
            <div class="doc-callout bg-red-50/50 border-red-500 dark:bg-red-900/10">
                <div class="callout-title text-red-700 dark:text-red-400">⚠ Batas Ukuran File Upload</div>
                <ul class="doc-ul mb-0">
                    <li>Setiap file yang diunggah maksimal berukuran 5 MB.</li>
                    <li>File yang melebihi 5 MB akan ditolak oleh sistem dan tidak dapat tersimpan.</li>
                    <li>Kompres atau ubah resolusi gambar terlebih dahulu jika ukuran melebihi batas.</li>
                    <li>Format didukung: IMAGE (JPG, PNG), PDF, dan lainnya sesuai konfigurasi.</li>
                </ul>
            </div>
            <img src="{{ asset('assets/panduan-images/page15_img09.png') }}" class="doc-image mt-4">
        </div>
        <div class="pdf-page-footer"><span>Halaman 15 dari 19</span></div>
    </div>

    {{-- PAGE 16 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 5 — Download ZIP</span></div>
        <div class="pdf-page-body">
            <p class="doc-p">Di bagian atas panel Dokumentasi terdapat tombol ‘DOWNLOAD ZIP’ (oranye) untuk mengunduh seluruh file dokumentasi kegiatan sekaligus dalam satu berkas .zip.</p>
            <h4 class="doc-subsection-title">Cara Menggunakan Download ZIP</h4>
            <div class="border rounded-lg p-2 dark:border-slate-700">
                <div class="doc-step"><div class="doc-step-num">1</div><div class="doc-step-desc"><div class="doc-step-title">Buka Detail</div>Pilih kegiatan dari daftar, lalu klik untuk membuka halaman detailnya.</div></div>
                <div class="doc-step"><div class="doc-step-num">2</div><div class="doc-step-desc"><div class="doc-step-title">Temukan Panel</div>Gulir ke bawah hingga menemukan panel ‘DOKUMENTASI’.</div></div>
                <div class="doc-step"><div class="doc-step-num">3</div><div class="doc-step-desc"><div class="doc-step-title">Periksa File</div>Pastikan file yang dibutuhkan tersedia dalam daftar.</div></div>
                <div class="doc-step border-none"><div class="doc-step-num">4</div><div class="doc-step-desc"><div class="doc-step-title">Klik Download ZIP</div>Klik tombol oranye ‘DOWNLOAD ZIP’ di pojok kanan atas panel.</div></div>
            </div>
            <h4 class="doc-subsection-title mt-8">Informasi File</h4>
            <table class="doc-table">
                <thead><tr><th class="w-1/4">Field</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td>Nama File</td><td>Nama lengkap file yang diunggah.</td></tr>
                    <tr><td>Jenis File</td><td>Tipe dokumen: IMAGE, PDF, DOCX, dll.</td></tr>
                    <tr><td>Ukuran File</td><td>Besar file dalam satuan MB (maks. 5 MB per file).</td></tr>
                    <tr><td>Ikon ↗</td><td>Klik untuk membuka/melihat satu file di tab baru.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pdf-page-footer"><span>Halaman 16 dari 19</span></div>
    </div>

    {{-- PAGE 17 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 5 — Monitoring</span></div>
        <div class="pdf-page-body">
            <h4 class="doc-subsection-title">5.2.4 Melihat Rekap Kegiatan</h4>
            <ul class="doc-ul">
                <li>Akses menu ‘Rekap Kegiatan’ untuk melihat realisasi anggaran seluruh kegiatan.</li>
                <li>Gunakan filter Tahun, Periode, dan Bulan lalu klik TAMPILKAN.</li>
                <li>Pantau persentase: hijau (baik) dan merah (perlu perhatian).</li>
            </ul>
            <h3 class="doc-section-title">5.2.5 Keterbatasan Akun Subbag</h3>
            <ul class="doc-ul">
                <li>Tidak dapat menghapus kegiatan — hanya Super Admin yang berwenang menghapus data.</li>
                <li>Tidak dapat mengelola akun pengguna lain.</li>
                <li>Tidak dapat mengakses menu Manajemen User dan konfigurasi sistem.</li>
            </ul>
            <img src="{{ asset('assets/panduan-images/page17_img10.png') }}" class="doc-image mt-6">
        </div>
        <div class="pdf-page-footer"><span>Halaman 17 dari 19</span></div>
    </div>

    {{-- PAGE 18 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 6 — Keamanan</span></div>
        <div class="pdf-page-body">
            <div class="doc-chapter-label">Bab 6</div><h2 class="doc-chapter-title">Keamanan & Troubleshooting</h2>
            <h3 class="doc-section-title">6.1 Kebijakan Kata Sandi</h3>
            <ul class="doc-ul">
                <li>Password default adalah ‘rahasia’ — segera ganti setelah login pertama kali.</li>
                <li>Gunakan kata sandi minimal 8 karakter dengan kombinasi huruf dan angka.</li>
                <li>Jangan ganti kata sandi secara berkala, minimal setiap 3 bulan.</li>
                <li>Jika lupa kata sandi, hubungi Super Admin untuk reset.</li>
            </ul>
            <h3 class="doc-section-title">6.2 Hak Akses Ringkasan</h3>
            <table class="doc-table">
                <thead><tr><th>Fitur</th><th class="text-center">Admin</th><th class="text-center">Subbag</th></tr></thead>
                <tbody>
                    <tr><td>Melihat Semua Kegiatan</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
                    <tr><td>Menambah/Edit Kegiatan</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
                    <tr><td>Upload Dokumentasi</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
                    <tr><td>Menghapus Kegiatan</td><td class="text-center">✔</td><td class="text-center">✘</td></tr>
                    <tr><td>Kelola User / User Baru</td><td class="text-center">✔</td><td class="text-center">✘</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pdf-page-footer"><span>Halaman 18 dari 19</span></div>
    </div>

    {{-- PAGE 19 --}}
    <div class="pdf-page">
        <div class="pdf-page-header"><span>{{$pHeader}}</span><span>Bab 6 — Solusi</span></div>
        <div class="pdf-page-body">
            <h3 class="doc-section-title">6.3 Masalah Umum & Solusi</h3>
            <table class="doc-table">
                <thead><tr><th class="w-1/3">Masalah</th><th>Solusi</th></tr></thead>
                <tbody>
                    <tr><td>Lupa kata sandi</td><td>Hubungi Super Admin untuk reset password.</td></tr>
                    <tr><td>File upload ditolak</td><td>Pastikan ukuran file tidak melebihi 5 MB. Kompres file jika perlu.</td></tr>
                    <tr><td>Data rekap tidak muncul</td><td>Pastikan filter Tahun, Periode, Bulan sudah sesuai dan klik TAMPILKAN.</td></tr>
                    <tr><td>Halaman tidak terbuka</td><td>Periksa koneksi internet atau bersihkan cache browser.</td></tr>
                    <tr><td>Sesi berakhir</td><td>Sesi otomatis berakhir saat lama tidak aktif. Silakan login kembali.</td></tr>
                </tbody>
            </table>
            <div class="mt-20 text-center border-t border-slate-100 dark:border-slate-800 pt-10">
                <p class="text-xs font-bold text-slate-400">Buku Panduan ini diterbitkan oleh:</p>
                <p class="text-sm font-extrabold text-slate-600 dark:text-slate-200 mt-1 uppercase">KPU Kabupaten Pasuruan</p>
                <p class="text-xs text-slate-400">Tahun 2026</p>
            </div>
        </div>
        <div class="pdf-page-footer"><span>Tahun 2026</span><span>Halaman 19 dari 19</span></div>
    </div>
</div>
</body>
</html>
