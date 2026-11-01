<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->date('tanggal_akhir_jadwal_rencana')->nullable()->after('tanggal_akhir_jadwal');
        });

        DB::table('pemangkasans')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $rencana = $row->tanggal_akhir_jadwal ?? $row->tanggal_eksekusi;

                DB::table('pemangkasans')->where('id', $row->id)->update([
                    'tanggal_akhir_jadwal_rencana' => $rencana,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn('tanggal_akhir_jadwal_rencana');
        });
    }
};
