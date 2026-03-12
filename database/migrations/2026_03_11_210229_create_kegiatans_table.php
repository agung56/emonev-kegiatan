<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->year('tahun_anggaran');
            $table->enum('kepemilikan', ['lembaga', 'sekretariat']);
            $table->foreignId('pagu_id')->constrained('pagus')->onDelete('restrict');
            $table->foreignId('sasaran_id')->constrained('sasarans')->onDelete('restrict');
            $table->string('nama_kegiatan');
            $table->string('lokus')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('output_kegiatan')->nullable();
            $table->text('kendala_kegiatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
