<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagus', function (Blueprint $table) {
            if (! Schema::hasColumn('pagus', 'program')) {
                $table->string('program')->nullable()->after('kegiatan');
            }
        });

        Schema::table('pagu_komponens', function (Blueprint $table) {
            if (! Schema::hasColumn('pagu_komponens', 'nama_kegiatan')) {
                $table->string('nama_kegiatan')->nullable()->after('nama_komponen');
            }
        });

        Schema::table('pagu_details', function (Blueprint $table) {
            if (! Schema::hasColumn('pagu_details', 'ro')) {
                $table->string('ro')->nullable()->after('pagu_komponen_id');
            }

            if (! Schema::hasColumn('pagu_details', 'komponen_label')) {
                $table->string('komponen_label')->nullable()->after('ro');
            }

            if (! Schema::hasColumn('pagu_details', 'sub_komponen')) {
                $table->string('sub_komponen')->nullable()->after('komponen_label');
            }

            if (! Schema::hasColumn('pagu_details', 'detail')) {
                $table->string('detail')->nullable()->after('sub_komponen');
            }
        });

        DB::table('pagus')
            ->select('id', 'kegiatan', 'program')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                if (blank($row->program) && filled($row->kegiatan)) {
                    DB::table('pagus')
                        ->where('id', $row->id)
                        ->update(['program' => $row->kegiatan]);
                }
            });

        DB::table('pagu_komponens')
            ->select('id', 'nama_komponen', 'nama_kegiatan')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                if (blank($row->nama_kegiatan) && filled($row->nama_komponen)) {
                    DB::table('pagu_komponens')
                        ->where('id', $row->id)
                        ->update(['nama_kegiatan' => $row->nama_komponen]);
                }
            });

        DB::table('pagu_details')
            ->select('id', 'nama_akun', 'detail')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                if (blank($row->detail) && filled($row->nama_akun)) {
                    DB::table('pagu_details')
                        ->where('id', $row->id)
                        ->update(['detail' => $row->nama_akun]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pagu_details', function (Blueprint $table) {
            if (Schema::hasColumn('pagu_details', 'detail')) {
                $table->dropColumn('detail');
            }

            if (Schema::hasColumn('pagu_details', 'sub_komponen')) {
                $table->dropColumn('sub_komponen');
            }

            if (Schema::hasColumn('pagu_details', 'komponen_label')) {
                $table->dropColumn('komponen_label');
            }

            if (Schema::hasColumn('pagu_details', 'ro')) {
                $table->dropColumn('ro');
            }
        });

        Schema::table('pagu_komponens', function (Blueprint $table) {
            if (Schema::hasColumn('pagu_komponens', 'nama_kegiatan')) {
                $table->dropColumn('nama_kegiatan');
            }
        });

        Schema::table('pagus', function (Blueprint $table) {
            if (Schema::hasColumn('pagus', 'program')) {
                $table->dropColumn('program');
            }
        });
    }
};
