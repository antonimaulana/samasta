<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            $table->string('foto_sebelum_1')->nullable();
            $table->string('foto_sebelum_2')->nullable();
            $table->string('foto_saat_1')->nullable();
            $table->string('foto_saat_2')->nullable();
            $table->string('foto_sesudah_1')->nullable();
            $table->string('foto_sesudah_2')->nullable();
        });

        if (Schema::hasColumn('pemeliharaan_tamans', 'foto_sebelum')
            || Schema::hasColumn('pemeliharaan_tamans', 'foto_sesudah')) {
            foreach (DB::table('pemeliharaan_tamans')->get() as $record) {
                DB::table('pemeliharaan_tamans')->where('id', $record->id)->update([
                    'foto_sebelum_1' => $record->foto_sebelum ?? null,
                    'foto_sesudah_1' => $record->foto_sesudah ?? null,
                ]);
            }
        }

        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            if (Schema::hasColumn('pemeliharaan_tamans', 'foto_sebelum')) {
                $table->dropColumn('foto_sebelum');
            }
            if (Schema::hasColumn('pemeliharaan_tamans', 'foto_sesudah')) {
                $table->dropColumn('foto_sesudah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
        });

        foreach (DB::table('pemeliharaan_tamans')->get() as $record) {
            DB::table('pemeliharaan_tamans')->where('id', $record->id)->update([
                'foto_sebelum' => $record->foto_sebelum_1,
                'foto_sesudah' => $record->foto_sesudah_1,
            ]);
        }

        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
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
