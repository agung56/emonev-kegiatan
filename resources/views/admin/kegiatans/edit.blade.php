@extends('layouts.app')
@section('page_title', 'Edit Kegiatan')

@section('content')
<div class="p-6 space-y-6" x-data="kegiatanForm()">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('kegiatans.show', $kegiatan->id) }}"
           class="p-2.5 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Edit Kegiatan</h1>
            <p class="text-sm text-slate-500 font-medium truncate max-w-lg">{{ $kegiatan->nama_kegiatan }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="flex flex-col gap-2 px-5 py-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-sm">
        <span class="font-black uppercase text-xs tracking-widest">Terdapat kesalahan input:</span>
        @foreach($errors->all() as $error)<span class="font-medium">• {{ $error }}</span>@endforeach
    </div>
    @endif

    <form action="{{ route('kegiatans.update', $kegiatan->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

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
                    <input type="number" name="tahun_anggaran" value="{{ old('tahun_anggaran', $kegiatan->tahun_anggaran) }}" min="2000" max="2099" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Kepemilikan <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="kepemilikan" required class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="lembaga" {{ old('kepemilikan',$kegiatan->kepemilikan)==='lembaga'?'selected':'' }}>Lembaga</option>
                            <option value="sekretariat" {{ old('kepemilikan',$kegiatan->kepemilikan)==='sekretariat'?'selected':'' }}>Sekretariat</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                </div>
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan',$kegiatan->nama_kegiatan) }}" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Lokus / Tempat</label>
                    <input type="text" name="lokus" value="{{ old('lokus',$kegiatan->lokus) }}" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai',$kegiatan->tanggal_mulai->format('Y-m-d')) }}" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai',$kegiatan->tanggal_selesai->format('Y-m-d')) }}" required class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none transition-all">
                </div>
            </div>
        </div>

        {{-- SECTION 2: Pagu & Anggaran --}}
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
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Pagu Anggaran <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="pagu_id" required @change="loadPaguDetails($event.target.value)" class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="">— Pilih Pagu —</option>
                            @foreach($pagus as $pagu)
                            <option value="{{ $pagu->id }}" {{ old('pagu_id',$kegiatan->pagu_id)==$pagu->id?'selected':'' }}>{{ $pagu->kegiatan }} ({{ $pagu->tahun_anggaran }}) — Rp {{ number_format($pagu->total_nominal,0,',','.') }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                </div>

                {{-- Info total pagu & sisa --}}
                <div x-show="paguTotal > 0" x-transition class="grid grid-cols-3 gap-3">
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Pagu</p>
                        <p class="text-sm font-black text-slate-700 dark:text-white mt-1" x-text="formatRupiahFull(paguTotal)"></p>
                    </div>
                    <div class="p-3.5 bg-brand-primary/5 dark:bg-brand-primary/10 rounded-2xl">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Digunakan</p>
                        <p class="text-sm font-black text-brand-primary mt-1" x-text="formatRupiahFull(totalAnggaran)"></p>
                    </div>
                    <div class="p-3.5 rounded-2xl" :class="sisaAnggaran < 0 ? 'bg-red-50 dark:bg-red-500/10' : 'bg-green-50 dark:bg-green-500/10'">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sisa Anggaran</p>
                        <p class="text-sm font-black mt-1" :class="sisaClass" x-text="formatRupiahFull(sisaAnggaran)"></p>
                    </div>
                </div>

                <div x-show="anggaranRows.length > 0" x-transition class="space-y-3">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Penggunaan Per Akun</label>
                    <template x-for="(row, index) in anggaranRows" :key="index">
                        <div class="group flex flex-col md:flex-row items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-white/5 hover:border-brand-primary/30 transition-all">
                            <input type="hidden" :name="`anggaran[${index}][id]`" :value="row.id ?? ''">
                            <div class="flex-1 w-full">
                                <div class="relative">
                                    <select :name="`anggaran[${index}][pagu_detail_id]`" x-model="row.pagu_detail_id"
                                            class="appearance-none w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-8">
                                        <option value="">— Pilih Akun —</option>
                                        <template x-for="d in paguDetails" :key="d.id">
                                            <option :value="d.id" :selected="row.pagu_detail_id == d.id" x-text="`${d.nama_akun} — Rp ${Number(d.nominal).toLocaleString('id-ID')}`"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M19 9l-7 7-7-7"></path></svg></div>
                                </div>
                                {{-- Sisa per akun --}}
                                <template x-if="row.pagu_detail_id">
                                    <div class="flex items-center justify-between mt-1.5 px-1">
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            Pagu akun: <span class="font-black text-slate-600 dark:text-slate-300" x-text="formatRupiahFull(getNominalAkun(row.pagu_detail_id))"></span>
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
                    Tambah Akun
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
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Sasaran <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="sasaran_id" required @change="loadIndikators($event.target.value)" class="appearance-none w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-bold outline-none cursor-pointer transition-all pr-10">
                            <option value="">— Pilih Sasaran —</option>
                            @foreach($sasarans as $sasaran)
                            <option value="{{ $sasaran->id }}" {{ old('sasaran_id',$kegiatan->sasaran_id)==$sasaran->id?'selected':'' }}>{{ $sasaran->nama_sasaran }} ({{ ucfirst($sasaran->kepemilikan) }})</option>
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
                                <input type="checkbox" name="indikator_ids[]" :value="ind.id"
                                       :checked="selectedIndikatorIds.includes(ind.id)"
                                       class="mt-0.5 w-4 h-4 rounded-md accent-brand-primary cursor-pointer shrink-0">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover/ind:text-slate-900 dark:group-hover/ind:text-white leading-snug" x-text="ind.nama_indikator"></span>
                            </label>
                        </template>
                    </div>
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
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Output Kegiatan</label>
                    <textarea name="output_kegiatan" rows="5" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-semibold outline-none transition-all resize-none">{{ old('output_kegiatan',$kegiatan->output_kegiatan) }}</textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Kendala Kegiatan</label>
                    <textarea name="kendala_kegiatan" rows="5" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-brand-primary rounded-2xl text-sm text-slate-800 dark:text-white font-semibold outline-none transition-all resize-none">{{ old('kendala_kegiatan',$kegiatan->kendala_kegiatan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- SECTION 5: Dokumentasi --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden"
             x-data="fileManager()">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-brand-primary/10 flex items-center justify-center"><span class="text-brand-primary font-black text-sm">5</span></div>
                    <div>
                        <h2 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest">Dokumentasi Kegiatan</h2>
                        <p class="text-[10px] text-slate-400 font-medium">Centang file lama untuk dihapus · Upload file baru untuk ditambahkan</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="existingFiles.filter(f=>!f.deleted).length > 0"
                          class="px-2.5 py-1 bg-slate-100 dark:bg-white/10 text-slate-500 rounded-full text-[10px] font-black"
                          x-text="`${existingFiles.filter(f=>!f.deleted).length} tersimpan`"></span>
                    <span x-show="newFiles.length > 0"
                          class="px-2.5 py-1 bg-brand-primary/10 text-brand-primary rounded-full text-[10px] font-black"
                          x-text="`+${newFiles.length} baru`"></span>
                </div>
            </div>
            <div class="p-6 space-y-5">

                {{-- ── File Lama ── --}}
                @if($kegiatan->dokumens->count() > 0)
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">File Tersimpan</span>
                        <button type="button" @click="markAllDeleted()"
                                class="text-[10px] font-black text-red-400 hover:text-red-600 uppercase tracking-widest transition-colors">
                            Tandai Hapus Semua
                        </button>
                    </div>

                    @foreach($kegiatan->dokumens as $dok)
                    <div x-data="{ id: {{ $dok->id }}, deleted: false }"
                         x-init="registerExisting(id)"
                         class="group flex items-center gap-3 p-3.5 rounded-2xl border-2 transition-all"
                         :class="deleted
                            ? 'bg-red-50 dark:bg-red-500/5 border-red-200 dark:border-red-500/20 opacity-60'
                            : 'bg-slate-50 dark:bg-slate-800/50 border-transparent hover:border-slate-200'">

                        {{-- Ikon file --}}
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 text-xs font-black uppercase
                            {{ $dok->tipe_file==='pdf' ? 'bg-red-100 text-red-500' : '' }}
                            {{ $dok->tipe_file==='image' ? 'bg-blue-100 text-blue-500' : '' }}
                            {{ $dok->tipe_file==='word' ? 'bg-indigo-100 text-indigo-500' : '' }}
                            {{ $dok->tipe_file==='excel' ? 'bg-green-100 text-green-500' : '' }}">
                            @php $ext = pathinfo($dok->nama_file, PATHINFO_EXTENSION) @endphp
                            {{ strtoupper($ext) }}
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate" :class="deleted ? 'line-through' : ''">{{ $dok->nama_file }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-slate-400 font-medium uppercase">{{ strtoupper($dok->tipe_file) }}</span>
                                @if($dok->ukuran_file)
                                <span class="text-[10px] text-slate-400">• {{ number_format($dok->ukuran_file/1024/1024, 2) }} MB</span>
                                @endif
                                <span x-show="deleted" class="text-[10px] font-black text-red-500 uppercase tracking-widest">akan dihapus</span>
                            </div>
                        </div>

                        {{-- Hidden input hapus --}}
                        <input type="checkbox" name="hapus_dokumen[]" value="{{ $dok->id }}"
                               x-model="deleted"
                               @change="toggleDeleted(id, deleted)"
                               class="hidden">

                        {{-- Aksi --}}
                        <div class="flex items-center gap-1 shrink-0">
                            <a href="{{ Storage::url($dok->path_file) }}" target="_blank"
                               class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-all" title="Buka file">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <button type="button"
                                    @click="deleted = !deleted; toggleDeleted(id, deleted)"
                                    class="p-1.5 rounded-lg transition-all"
                                    :class="deleted
                                        ? 'text-green-500 hover:bg-green-50 dark:hover:bg-green-500/10'
                                        : 'text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10'"
                                    :title="deleted ? 'Batalkan hapus' : 'Tandai untuk dihapus'">
                                <template x-if="!deleted">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </template>
                                <template x-if="deleted">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </template>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-3">
                    <div class="flex-1 border-t border-slate-100 dark:border-white/5"></div>
                    <span class="text-[10px] font-black text-slate-300 dark:text-slate-600 uppercase tracking-widest">Upload Baru</span>
                    <div class="flex-1 border-t border-slate-100 dark:border-white/5"></div>
                </div>
                @endif

                {{-- ── Dropzone Upload Baru ── --}}
                <div class="relative border-2 border-dashed rounded-2xl p-7 text-center transition-all cursor-pointer"
                     :class="isDragging ? 'border-brand-primary bg-brand-primary/5 scale-[1.005]' : 'border-slate-200 dark:border-white/10 hover:border-brand-primary/40 hover:bg-slate-50/50'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)"
                     @click="$refs.newFileInput.click()">
                    <input type="file" x-ref="newFileInput" id="newDokumenInput" name="dokumen[]" multiple
                           accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx"
                           class="hidden"
                           @change="handleFiles($event.target.files)">
                    <div class="flex flex-col items-center gap-2.5 pointer-events-none">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                             :class="isDragging ? 'bg-brand-primary/10' : 'bg-slate-100 dark:bg-white/5'">
                            <svg class="w-6 h-6 transition-colors" :class="isDragging ? 'text-brand-primary' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-600 dark:text-slate-300">Seret atau <span class="text-brand-primary">klik untuk browse</span></p>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">PDF · JPG · PNG · DOCX · XLSX — Maks 10MB/file</p>
                        </div>
                    </div>
                </div>

                {{-- Daftar file baru --}}
                <div x-show="newFiles.length > 0" x-transition class="space-y-2.5">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest" x-text="`${newFiles.length} file baru`"></span>
                        <button type="button" @click="clearNewFiles()"
                                class="flex items-center gap-1 text-[10px] font-black text-red-400 hover:text-red-600 uppercase tracking-widest transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Hapus Semua Baru
                        </button>
                    </div>

                    <template x-for="(file, i) in newFiles" :key="i">
                        <div class="group flex items-center gap-3 p-3.5 rounded-2xl border-2 transition-all"
                             :class="file.error
                                ? 'bg-red-50 dark:bg-red-500/5 border-red-200 dark:border-red-500/20'
                                : 'bg-slate-50 dark:bg-slate-800/50 border-transparent hover:border-slate-200'">

                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 overflow-hidden text-xs font-black uppercase"
                                 :class="{
                                     'bg-red-100 text-red-500': file.type === 'pdf',
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

                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate" x-text="file.name"></p>
                                <p class="text-[10px] mt-0.5 font-medium"
                                   :class="file.error ? 'text-red-500' : 'text-slate-400'"
                                   x-text="file.error || file.size"></p>
                            </div>

                            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <label class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg cursor-pointer transition-all" title="Ganti file">
                                    <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx" @change="replaceNewFile(i, $event.target.files[0])">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </label>
                                <button type="button" @click="removeNewFile(i)"
                                        class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="newFiles.some(f=>f.error)" x-transition class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">Beberapa file melebihi 10MB. Hapus atau ganti sebelum menyimpan.</span>
                </div>

            </div>
        </div>

        {{-- Submit --}}
        <div class="flex flex-col md:flex-row gap-4 pb-6">
            <a href="{{ route('kegiatans.show', $kegiatan->id) }}" class="flex-1 py-4 text-center text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all border-2 border-slate-200 dark:border-white/10 rounded-2xl hover:bg-slate-50 dark:hover:bg-white/5">Batal</a>
            <button type="submit" class="flex-[3] py-4 bg-brand-primary text-brand-black text-xs font-black rounded-2xl hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-brand-primary/20 uppercase tracking-widest cursor-pointer">Perbarui Data Kegiatan</button>
        </div>

    </form>
</div>

<script>
function kegiatanForm() {
    return {
        paguDetails: @json($kegiatan->pagu->details ?? []),
        indikators:  @json($kegiatan->sasaran->indikators ?? []),
        selectedIndikatorIds: @json($kegiatan->indikators->pluck('id')),
        anggaranRows: @json($kegiatan->anggarans->map(fn($a) => ['id' => $a->id, 'pagu_detail_id' => $a->pagu_detail_id, 'nominal' => $a->nominal_digunakan])),

        paguTotal: @json($kegiatan->pagu->details->sum('nominal') ?? 0),
        paguNamaAkun: @json(
            $kegiatan->pagu->details->mapWithKeys(fn($d) => [
                $d->id => ['nama' => $d->nama_akun, 'nominal' => (float)$d->nominal]
            ]) ?? collect()
        ),

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
            return this.paguNamaAkun[paguDetailId]?.nominal || 0;
        },
        getSisaAkun(paguDetailId, rowIndex) {
            const paguAkun = this.getNominalAkun(paguDetailId);
            if (!paguAkun) return null;
            // Total terpakai di akun ini (semua row yang pakai akun yg sama)
            const terpakai = this.anggaranRows.reduce((s, r, i) => {
                return s + (r.pagu_detail_id == paguDetailId ? (parseInt(r.nominal) || 0) : 0);
            }, 0);
            return paguAkun - terpakai;
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
            // Bangun mapping id → {nama, nominal} dan total pagu dari sum detail
            this.paguNamaAkun = {};
            this.paguDetails.forEach(d => {
                this.paguNamaAkun[d.id] = { nama: d.nama_akun, nominal: parseFloat(d.nominal) };
            });
            this.paguTotal = this.paguDetails.reduce((s, d) => s + parseFloat(d.nominal), 0);
        },

        async loadIndikators(id) {
            this.indikators = []; this.selectedIndikatorIds = [];
            if (!id) return;
            this.indikators = await fetch(`/api/sasaran/${id}/indikators`).then(r => r.json());
        },

        addAnggaranRow() { this.anggaranRows.push({ id: null, pagu_detail_id: '', nominal: 0 }); },
        removeAnggaranRow(i) { this.anggaranRows.splice(i, 1); },
    }
}

function fileManager() {
    return {
        existingFiles: [],   // { id, deleted }
        newFiles: [],        // metas file baru
        isDragging: false,
        _dt: new DataTransfer(),

        registerExisting(id) {
            if (!this.existingFiles.find(f => f.id === id)) {
                this.existingFiles.push({ id, deleted: false });
            }
        },

        toggleDeleted(id, val) {
            const f = this.existingFiles.find(x => x.id === id);
            if (f) f.deleted = val;
        },

        markAllDeleted() {
            this.existingFiles.forEach(f => {
                f.deleted = true;
                // Sync checkbox DOM
                const cb = document.querySelector(`input[name="hapus_dokumen[]"][value="${f.id}"]`);
                if (cb) cb.checked = true;
            });
        },

        _type(ext) {
            if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'image';
            if (ext === 'pdf') return 'pdf';
            if (['doc','docx'].includes(ext)) return 'word';
            return 'excel';
        },

        _meta(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            const mb  = file.size / 1024 / 1024;
            const meta = {
                name: file.name, ext,
                size: mb < 1 ? `${(file.size/1024).toFixed(1)} KB` : `${mb.toFixed(2)} MB`,
                type: this._type(ext),
                preview: null,
                error: mb > 10 ? `Terlalu besar (${mb.toFixed(1)} MB, maks 10 MB)` : null,
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
                if (this.newFiles.find(x => x.name === f.name && x.raw?.size === f.size)) return;
                this.newFiles.push(this._meta(f));
                this._dt.items.add(f);
            });
            this._sync();
        },

        handleDrop(e) {
            this.isDragging = false;
            this.handleFiles(e.dataTransfer.files);
        },

        removeNewFile(i) {
            this.newFiles.splice(i, 1);
            this._rebuild();
        },

        replaceNewFile(i, newFile) {
            if (!newFile) return;
            this.newFiles[i] = this._meta(newFile);
            this._rebuild();
        },

        clearNewFiles() {
            this.newFiles = [];
            this._dt = new DataTransfer();
            this._sync();
        },

        _rebuild() {
            this._dt = new DataTransfer();
            this.newFiles.forEach(f => { if (f.raw) this._dt.items.add(f.raw); });
            this._sync();
        },

        _sync() {
            const el = document.getElementById('newDokumenInput');
            if (el) el.files = this._dt.files;
        },
    }
}
</script>

<style>[x-cloak]{display:none!important}</style>
@endsection
