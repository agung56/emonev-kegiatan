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
        Schema::table('users', function (Blueprint $table) {
            // Kita gunakan constrained() untuk foreign key
            // nullable() agar user bisa tidak terikat ke sub bagian
            // after('role') hanya untuk kerapihan struktur tabel di database
            $table->foreignId('sub_bagian_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('sub_bagians')
                  ->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus constraint foreign key dulu baru drop kolomnya
            $table->dropForeign(['sub_bagian_id']);
            $table->dropColumn('sub_bagian_id');
        });
    }
};
