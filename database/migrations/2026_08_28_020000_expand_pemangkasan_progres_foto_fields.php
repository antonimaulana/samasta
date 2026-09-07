<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->string('foto_sebelum_1')->nullable()->after('jumlah_personil');
            $table->string('foto_sebelum_2')->nullable()->after('foto_sebelum_1');
            $table->string('foto_saat_1')->nullable()->after('foto_sebelum_2');
            $table->string('foto_saat_2')->nullable()->after('foto_saat_1');
            $table->string('foto_sesudah_1')->nullable()->after('foto_saat_2');
            $table->string('foto_sesudah_2')->nullable()->after('foto_sesudah_1');
        });

        DB::table('pemangkasan_progres')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('pemangkasan_progres')->where('id', $row->id)->update([
                    'foto_sebelum_1' => $row->foto_sebelum,
                    'foto_saat_1' => $row->foto_saat,
                    'foto_sesudah_1' => $row->foto_sesudah,
                ]);
            }
        });

        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->dropColumn(['foto_sebelum', 'foto_saat', 'foto_sesudah']);
        });
    }

    public function down(): void
    {
        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->string('foto_sebelum')->nullable()->after('jumlah_personil');
            $table->string('foto_saat')->nullable()->after('foto_sebelum');
            $table->string('foto_sesudah')->nullable()->after('foto_saat');
        });

        DB::table('pemangkasan_progres')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('pemangkasan_progres')->where('id', $row->id)->update([
                    'foto_sebelum' => $row->foto_sebelum_1,
                    'foto_saat' => $row->foto_saat_1,
                    'foto_sesudah' => $row->foto_sesudah_1,
                ]);
            }
        });

        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->dropColumn([
                'foto_sebelum_1',
                'foto_sebelum_2',
                'foto_saat_1',
                'foto_saat_2',
                'foto_sesudah_1',
                'foto_sesudah_2',
            ]);
        });
    }
};
