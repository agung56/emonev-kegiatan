@extends('layouts.app')
@section('page_title', 'Tambah Kegiatan')

@section('content')
<div class="p-6 space-y-6" x-data="kegiatanForm()">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('kegiatans.index') }}"
           class="p-2.5 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Tambah Kegiatan</h1>
            <p class="text-sm text-slate-500 font-medium">Isi formulir data kegiatan dengan lengkap</p>
        </div>
    </div>

    @if($errors->any())
    <div class="flex flex-col gap-2 px-5 py-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-sm">
        <span class="font-black uppercase text-xs tracking-widest">Terdapat kesalahan input:</span>
        @foreach($errors->all() as $error)<span class="font-medium">• {{ $error }}</span>@endforeach
    </div>
    @endif

    <form action="{{ route('kegiatans.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- SECTION 1: Informasi Dasar --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center"><span class="text-brand-primary font-black text-sm">1</span></div>
                <div>
                    <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Informasi Dasar</h2>
                    <p class="text-[10px] text-slate-400 font-medium">Tahun, kepemilikan, dan nama kegiatan</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Tahun Anggaran <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun_anggaran" value="{{ old('tahun_anggaran', date('Y')) }}" min="2000" max="2099" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Kepemilikan <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="kepemilikan" required class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="">— Pilih —</option>
                            <option value="lembaga" {{ old('kepemilikan')==='lembaga'?'selected':'' }}>Lembaga</option>
                            <option value="sekretariat" {{ old('kepemilikan')==='sekretariat'?'selected':'' }}>Sekretariat</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                </div>
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required placeholder="Contoh: Pelatihan Manajemen Keuangan Daerah" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all placeholder:font-normal placeholder:text-slate-400">
                </div>
                @if(auth()->user()->role === 'admin')
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Sub Bagian Pelaksana <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="sub_bagian_id" required class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="">— Pilih Sub Bagian —</option>
                            @foreach($subBagians as $subBagian)
                            <option value="{{ $subBagian->id }}" {{ old('sub_bagian_id') == $subBagian->id ? 'selected' : '' }}>{{ $subBagian->nama_sub_bagian }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                </div>
                @endif
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Lokus / Tempat</label>
                    <input type="text" name="lokus" value="{{ old('lokus') }}" placeholder="Contoh: Aula Gedung A" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all placeholder:font-normal placeholder:text-slate-400">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
            </div>
        </div>

        {{-- SECTION 2: Pagu Anggaran --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center"><span class="text-brand-primary font-black text-sm">2</span></div>
                <div>
                    <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Pagu & Penggunaan Anggaran</h2>
                    <p class="text-[10px] text-slate-400 font-medium">Pilih pagu lalu isi penggunaan per akun</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Pagu Anggaran</label>
                    <div class="relative">
                        <select name="pagu_id" @change="loadPaguDetails($event.target.value)" class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="">— Pilih Pagu Anggaran —</option>
                            @foreach($pagus as $pagu)
                            <option value="{{ $pagu->id }}" {{ old('pagu_id')==$pagu->id?'selected':'' }}>{{ $pagu->kegiatan }} ({{ $pagu->tahun_anggaran }}) — Rp {{ number_format($pagu->total_nominal,0,',','.') }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p class="text-[10px] text-slate-400 ml-1">Opsional jika tidak ada anggaran yang dikeluarkan.</p>
                </div>

                {{-- Info total pagu & sisa — muncul setelah pilih pagu --}}
                <div x-show="paguTotal > 0" x-transition class="grid grid-cols-3 gap-3">
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sisa Pagu Tersedia</p>
                        <p class="text-sm font-black text-slate-700 dark:text-white mt-1" x-text="formatRupiahFull(paguTotal)"></p>
                        <p class="text-[9px] text-slate-400 mt-0.5">(sudah dikurangi kegiatan lain)</p>
                    </div>
                    <div class="p-3.5 bg-brand-primary/5 dark:bg-brand-primary/10 rounded-2xl">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Input Form Ini</p>
                        <p class="text-sm font-black text-brand-primary mt-1" x-text="formatRupiahFull(totalAnggaran)"></p>
                    </div>
                    <div class="p-3.5 rounded-2xl" :class="sisaAnggaran < 0 ? 'bg-red-50 dark:bg-red-500/10' : 'bg-green-50 dark:bg-green-500/10'">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sisa Setelah Input</p>
                        <p class="text-sm font-black mt-1" :class="sisaClass" x-text="formatRupiahFull(sisaAnggaran)"></p>
                    </div>
                </div>

                <div x-show="anggaranRows.length > 0" class="space-y-3" x-transition>
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Penggunaan Per Akun</label>
                    <template x-for="(row, index) in anggaranRows" :key="index">
                        <div class="group flex flex-col md:flex-row items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-white/5 hover:border-brand-primary/30 transition-all">
                            <div class="flex-1 w-full">
                                <div class="relative">
                                    <select :name="`anggaran[${index}][pagu_detail_id]`" x-model="row.pagu_detail_id"
                                            class="appearance-none w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-8">
                                        <option value="">— Pilih Akun —</option>
                                        <template x-for="d in paguDetails" :key="d.id">
                                            <option :value="d.id" x-text="`${d.nama_komponen ? `${d.nama_komponen} • ` : ''}${d.nama_akun} — Rp ${Number(d.nominal).toLocaleString('id-ID')}`"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                                </div>
                                {{-- Sisa per akun --}}
                                <template x-if="row.pagu_detail_id">
                                    <div class="flex items-center justify-between mt-1.5 px-1">
                                        <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1.5 flex-wrap">
                                            <span>Pagu: <span class="font-black text-slate-600 dark:text-slate-300" x-text="formatRupiahFull(getNominalAkun(row.pagu_detail_id))"></span></span>
                                            <span class="text-slate-300">·</span>
                                            <span>Terpakai lain: <span class="font-black text-amber-500" x-text="formatRupiahFull(paguNamaAkun[row.pagu_detail_id]?.sudahTerpakai || 0)"></span></span>
                                        </span>
                                        <span class="text-[10px] font-black"
                                              :class="getSisaAkun(row.pagu_detail_id, index) < 0 ? 'text-red-500' : 'text-green-500'">
                                            Sisa: <span x-text="formatRupiahFull(getSisaAkun(row.pagu_detail_id, index))"></span>
                                        </span>
                                    </div>
                                </template>
                            </div>
                            <div class="w-full md:w-56 shrink-0 relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-xs font-black text-slate-400 pointer-events-none select-none">Rp</span>
                                <input type="text"
                                       :value="formatRupiah(row.nominal)"
                                       @input="handleNominalInput(index, $event.target.value)"
                                       @focus="$event.target.select()"
                                       placeholder="0"
                                       class="w-full pl-10 pr-5 py-3 bg-white dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-xl text-sm font-black text-right text-brand-primary outline-none transition-all placeholder:text-slate-300 placeholder:font-normal">
                                <input type="hidden" :name="`anggaran[${index}][nominal]`" :value="row.nominal">
                            </div>
                            <button type="button" @click="removeAnggaranRow(index)"
                                    class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </template>
                    <div class="flex justify-end">
                        <div class="flex items-center gap-4 px-5 py-3 bg-brand-primary/5 dark:bg-brand-primary/10 rounded-2xl">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Digunakan</span>
                            <span class="text-base font-black text-brand-primary" x-text="formatRupiahFull(totalAnggaran)"></span>
                        </div>
                    </div>
                </div>

                <button type="button" @click="addAnggaranRow()" x-show="paguDetails.length > 0"
                        class="flex items-center gap-2 px-4 py-2.5 border-2 border-dashed border-brand-primary/30 hover:border-brand-primary text-brand-primary rounded-2xl text-xs font-black uppercase tracking-widest transition-all hover:bg-brand-primary/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Akun Anggaran
                </button>
            </div>
        </div>

        {{-- SECTION 3: Sasaran & Indikator --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center"><span class="text-brand-primary font-black text-sm">3</span></div>
                <div>
                    <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Sasaran & Indikator</h2>
                    <p class="text-[10px] text-slate-400 font-medium">Pilih sasaran lalu centang indikator yang relevan</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Sasaran Kegiatan <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="sasaran_id" required @change="loadIndikators($event.target.value)" class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="">— Pilih Sasaran —</option>
                            @foreach($sasarans as $sasaran)
                            <option value="{{ $sasaran->id }}" {{ old('sasaran_id')==$sasaran->id?'selected':'' }}>{{ $sasaran->nama_sasaran }} ({{ ucfirst($sasaran->kepemilikan) }})</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                </div>
                <div x-show="indikators.length > 0" x-transition class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Indikator <span class="text-red-500">*</span> <span class="text-brand-primary/70 normal-case font-medium italic">(Pilih satu atau lebih)</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <template x-for="ind in indikators" :key="ind.id">
                            <label class="flex items-start gap-3 p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl cursor-pointer hover:bg-brand-primary/5 transition-all group/ind">
                                <input type="checkbox" name="indikator_ids[]" :value="ind.id" class="mt-0.5 w-4 h-4 rounded-md accent-brand-primary cursor-pointer shrink-0">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover/ind:text-slate-900 dark:group-hover/ind:text-white leading-snug" x-text="ind.nama_indikator"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div x-show="sasaranSelected && indikators.length === 0" class="flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Sasaran ini belum memiliki indikator.</span>
                </div>
            </div>
        </div>

        {{-- SECTION 4: Output & Kendala --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center"><span class="text-brand-primary font-black text-sm">4</span></div>
                <div><h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Output & Evaluasi</h2></div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Output Kegiatan <span class="text-red-500">*</span></label>
                    <textarea name="output_kegiatan" rows="5" required placeholder="Tuliskan output yang dicapai..." class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-semibold outline-none transition-all resize-none placeholder:font-normal placeholder:text-slate-400">{{ old('output_kegiatan') }}</textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Kendala Kegiatan <span class="text-red-500">*</span></label>
                    <textarea name="kendala_kegiatan" rows="5" required placeholder="Tuliskan kendala yang ditemui..." class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-semibold outline-none transition-all resize-none placeholder:font-normal placeholder:text-slate-400">{{ old('kendala_kegiatan') }}</textarea>
                </div>
            </div>
        </div>

        {{-- SECTION 5: Dokumentasi --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden"
             x-data="fileUploader()">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center"><span class="text-brand-primary font-black text-sm">5</span></div>
                    <div>
                        <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Dokumentasi Kegiatan <span class="text-red-500">*</span></h2>
                        <p class="text-[10px] text-slate-400 font-medium">PDF · Gambar · Word · Excel — maks 5MB/file</p>
                    </div>
                </div>
                <span x-show="files.length > 0"
                      class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary rounded-full text-[10px] font-black uppercase tracking-widest"
                      x-text="`${files.length} file`"></span>
            </div>
            <div class="p-6 space-y-4">

                {{-- Dropzone --}}
                <div class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer"
                     :class="isDragging ? 'border-brand-primary bg-brand-primary/5 scale-[1.005]' : 'border-slate-200 dark:border-white/10 hover:border-brand-primary/40 hover:bg-slate-50/50'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)"
                     @click="$refs.fileInput.click()">
                    <input type="file" x-ref="fileInput" id="dokumenInput" name="dokumen[]" multiple required
                           accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx"
                           class="hidden"
                           @change="handleFiles($event.target.files)">
                    <div class="flex flex-col items-center gap-3 pointer-events-none">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-colors"
                             :class="isDragging ? 'bg-brand-primary/10' : 'bg-slate-100 dark:bg-white/5'">
                            <svg class="w-7 h-7 transition-colors" :class="isDragging ? 'text-brand-primary' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-600 dark:text-slate-300">Seret file ke sini atau <span class="text-brand-primary">klik untuk browse</span></p>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">PDF · JPG · PNG · DOCX · XLSX — Maks 5MB per file</p>
                        </div>
                    </div>
                </div>

                {{-- File List --}}
                <div x-show="files.length > 0" x-transition class="space-y-2.5">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest" x-text="`${files.length} file dipilih`"></span>
                        <button type="button" @click="clearAll()"
                                class="flex items-center gap-1 text-[10px] font-black text-red-400 hover:text-red-600 uppercase tracking-widest transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Hapus Semua
                        </button>
                    </div>

                    <template x-for="(file, i) in files" :key="i">
                        <div class="group flex items-center gap-3 p-3.5 rounded-2xl border-2 transition-all"
                             :class="file.error
                                ? 'bg-red-50 dark:bg-red-500/5 border-red-200 dark:border-red-500/20'
                                : 'bg-slate-50 dark:bg-slate-800/50 border-transparent hover:border-slate-200 dark:hover:border-white/10'">

                            {{-- Ikon / Preview --}}
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 overflow-hidden text-xs font-black uppercase"
                                 :class="{
                                     'bg-red-100 text-red-500': file.type === 'pdf' && !file.preview,
                                     'bg-blue-100 text-blue-500': file.type === 'image' && !file.preview,
                                     'bg-indigo-100 text-indigo-500': file.type === 'word',
                                     'bg-green-100 text-green-500': file.type === 'excel',
                                     'bg-slate-100 text-slate-400': file.error,
                                 }">
                                <template x-if="file.preview">
                                    <img :src="file.preview" class="w-11 h-11 object-cover">
                                </template>
                                <template x-if="!file.preview">
                                    <span x-text="file.ext"></span>
                                </template>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate" x-text="file.name"></p>
                                <p class="text-[10px] mt-0.5 font-medium"
                                   :class="file.error ? 'text-red-500' : 'text-slate-400'"
                                   x-text="file.error || file.size"></p>
                            </div>

                            {{-- Aksi --}}
                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{-- Ganti --}}
                                <label class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg cursor-pointer transition-all" title="Ganti file ini">
                                    <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx" @change="replaceFile(i, $event.target.files[0])">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </label>
                                {{-- Hapus --}}
                                <button type="button" @click="removeFile(i)"
                                        class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all" title="Hapus file ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="hasError" x-transition class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">Beberapa file melebihi batas ukuran. Hapus atau ganti sebelum menyimpan.</span>
                </div>

            </div>
        </div>

        {{-- Submit --}}
        <div class="flex flex-col md:flex-row gap-4 pb-6">
            <a href="{{ route('kegiatans.index') }}" class="flex-1 py-4 text-center text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all border-2 border-slate-200 dark:border-white/10 rounded-2xl hover:bg-slate-50 dark:hover:bg-white/5">Batal</a>
            <button type="submit" class="flex-[3] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-brand-primary/20 uppercase tracking-widest cursor-pointer">Simpan Data Kegiatan</button>
        </div>

    </form>
</div>

<script>
function kegiatanForm() {
    return {
        paguDetails: [],
        indikators: [],
        anggaranRows: [],
        sasaranSelected: false,

        paguTotal: 0,
        paguNamaAkun: {},   // { pagu_detail_id: { nama, nominal } }

        get totalAnggaran() {
            return this.anggaranRows.reduce((s, r) => s + (parseInt(r.nominal) || 0), 0);
        },
        get sisaAnggaran() {
            return this.paguTotal - this.totalAnggaran;
        },
        get sisaClass() {
            return this.sisaAnggaran < 0 ? 'text-red-500' : 'text-green-500';
        },
        getNominalAkun(paguDetailId) {
            // Kembalikan nominal pagu asli akun (untuk ditampilkan)
            return this.paguNamaAkun[paguDetailId]?.nominal || 0;
        },
        getSisaTersediaAkun(paguDetailId) {
            // Sisa tersedia = nominal akun - sudah terpakai kegiatan lain
            return this.paguNamaAkun[paguDetailId]?.sisaTersedia ?? null;
        },
        getSisaAkun(paguDetailId, rowIndex) {
            const sisaTersedia = this.getSisaTersediaAkun(paguDetailId);
            if (sisaTersedia === null) return null;
            // Kurangi juga input di form ini (semua row yg pakai akun yg sama)
            const inputForm = this.anggaranRows.reduce((s, r) => {
                return s + (r.pagu_detail_id == paguDetailId ? (parseInt(r.nominal) || 0) : 0);
            }, 0);
            return sisaTersedia - inputForm;
        },

        formatRupiah(val) {
            if (!val && val !== 0) return '';
            return new Intl.NumberFormat('id-ID').format(val);
        },

        formatRupiahFull(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
        },

        handleNominalInput(index, val) {
            const n = val.replace(/[^0-9]/g, '');
            this.anggaranRows[index].nominal = n ? parseInt(n) : 0;
        },

        async loadPaguDetails(id) {
            this.paguDetails = []; this.anggaranRows = [];
            this.paguTotal = 0; this.paguNamaAkun = {};
            if (!id) return;
            this.paguDetails = await fetch(`/api/pagu/${id}/details`).then(r => r.json());
            this.paguNamaAkun = {};
            this.paguDetails.forEach(d => {
                // sisa_tersedia = nominal pagu akun - sudah terpakai kegiatan lain
                this.paguNamaAkun[d.id] = {
                    nama:           d.nama_komponen ? `${d.nama_komponen} - ${d.nama_akun}` : d.nama_akun,
                    nominal:        parseFloat(d.nominal),        // pagu asli akun
                    sudahTerpakai:  parseFloat(d.sudah_terpakai), // terpakai kegiatan lain
                    sisaTersedia:   parseFloat(d.sisa_tersedia),  // yg masih bisa dipakai
                };
            });
            // paguTotal = total sisa yang masih bisa diinput (bukan pagu kotor)
            this.paguTotal = this.paguDetails.reduce((s, d) => s + parseFloat(d.sisa_tersedia), 0);
        },

        async loadIndikators(id) {
            this.indikators = []; this.sasaranSelected = !!id;
            if (!id) return;
            this.indikators = await fetch(`/api/sasaran/${id}/indikators`).then(r => r.json());
        },

        addAnggaranRow() { this.anggaranRows.push({ pagu_detail_id: '', nominal: 0 }); },
        removeAnggaranRow(i) { this.anggaranRows.splice(i, 1); },
    }
}

function fileUploader() {
    return {
        files: [],
        isDragging: false,
        _dt: new DataTransfer(),

        get hasError() { return this.files.some(f => f.error); },

        _type(ext) {
            if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'image';
            if (ext === 'pdf') return 'pdf';
            if (['doc','docx'].includes(ext)) return 'word';
            return 'excel';
        },

        _meta(file) {
            const ext  = file.name.split('.').pop().toLowerCase();
            const mb   = file.size / 1024 / 1024;
            const meta = {
                name: file.name, ext,
                size: mb < 1 ? `${(file.size/1024).toFixed(1)} KB` : `${mb.toFixed(2)} MB`,
                type: this._type(ext),
                preview: null,
                error: mb > 5 ? `Terlalu besar (${mb.toFixed(1)} MB, maks 5 MB)` : null,
                raw: file,
            };
            if (meta.type === 'image' && !meta.error) {
                const r = new FileReader();
                r.onload = e => { meta.preview = e.target.result; };
                r.readAsDataURL(file);
            }
            return meta;
        },

        handleFiles(list) {
            Array.from(list).forEach(f => {
                if (this.files.find(x => x.name === f.name && x.raw?.size === f.size)) return;
                this.files.push(this._meta(f));
                this._dt.items.add(f);
            });
            this._sync();
        },

        handleDrop(e) {
            this.isDragging = false;
            this.handleFiles(e.dataTransfer.files);
        },

        removeFile(i) {
            this.files.splice(i, 1);
            this._rebuild();
        },

        replaceFile(i, newFile) {
            if (!newFile) return;
            this.files[i] = this._meta(newFile);
            this._rebuild();
        },

        clearAll() {
            this.files = [];
            this._dt = new DataTransfer();
            this._sync();
        },

        _rebuild() {
            this._dt = new DataTransfer();
            this.files.forEach(f => { if (f.raw) this._dt.items.add(f.raw); });
            this._sync();
        },

        _sync() {
            const el = document.getElementById('dokumenInput');
            if (el) el.files = this._dt.files;
        },
    }
}
</script>

<style>[x-cloak]{display:none!important}</style>
@endsection
