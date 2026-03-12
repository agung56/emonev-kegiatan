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
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropForeign(['pagu_id']);
            $table->foreignId('pagu_id')->nullable()->change();
            $table->foreign('pagu_id')->references('id')->on('pagus')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropForeign(['pagu_id']);
            $table->foreignId('pagu_id')->nullable(false)->change();
            $table->foreign('pagu_id')->references('id')->on('pagus')->restrictOnDelete();
        });
    }
};
