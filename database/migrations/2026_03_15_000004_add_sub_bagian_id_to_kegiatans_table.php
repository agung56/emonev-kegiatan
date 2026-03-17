<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->foreignId('sub_bagian_id')
                ->nullable()
                ->after('created_by')
                ->constrained('sub_bagians')
                ->nullOnDelete();
        });

        DB::table('kegiatans as k')
            ->join('users as u', 'u.id', '=', 'k.created_by')
            ->whereNull('k.sub_bagian_id')
            ->whereNotNull('u.sub_bagian_id')
            ->update([
                'k.sub_bagian_id' => DB::raw('u.sub_bagian_id'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_bagian_id');
        });
    }
};
