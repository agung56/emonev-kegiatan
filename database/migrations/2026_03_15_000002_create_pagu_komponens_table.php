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
        Schema::create('pagu_komponens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pagu_id')->constrained()->onDelete('cascade');
            $table->string('nama_komponen');
            $table->timestamps();
        });

        if (Schema::hasColumn('pagus', 'komponen_anggaran')) {
            $now = now();
            $legacyKomponens = DB::table('pagus')
                ->select('id', 'komponen_anggaran')
                ->whereNotNull('komponen_anggaran')
                ->where('komponen_anggaran', '!=', '')
                ->get();

            if ($legacyKomponens->isNotEmpty()) {
                DB::table('pagu_komponens')->insert(
                    $legacyKomponens->map(fn ($row) => [
                        'pagu_id'        => $row->id,
                        'nama_komponen'  => $row->komponen_anggaran,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ])->all()
                );
            }

            Schema::table('pagus', function (Blueprint $table) {
                $table->dropColumn('komponen_anggaran');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('pagus', 'komponen_anggaran')) {
            Schema::table('pagus', function (Blueprint $table) {
                $table->string('komponen_anggaran')->nullable()->after('kegiatan');
            });
        }

        $fallbackKomponens = DB::table('pagu_komponens')
            ->select('pagu_id', 'nama_komponen')
            ->orderBy('id')
            ->get()
            ->unique('pagu_id');

        foreach ($fallbackKomponens as $komponen) {
            DB::table('pagus')
                ->where('id', $komponen->pagu_id)
                ->update(['komponen_anggaran' => $komponen->nama_komponen]);
        }

        Schema::dropIfExists('pagu_komponens');
    }
};
