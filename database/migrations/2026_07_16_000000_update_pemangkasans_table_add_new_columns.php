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
            $table->enum('asal_permohonan', ['Instruksi', 'Laporan Masyarakat', 'Survei Lapangan'])
                ->default('Instruksi')
                ->after('jenis_pohon');
            $table->text('pendukung_pelaksanaan')->nullable()->after('kondisi_sebelum');
            $table->string('pelaksana')->nullable()->after('tanggal_eksekusi');
            $table->string('foto_sesudah')->nullable()->after('status');
        });

        foreach (DB::table('pemangkasans')->get() as $row) {
            DB::table('pemangkasans')->where('id', $row->id)->update([
                'pelaksana' => $row->petugas_lapangan,
            ]);
        }

        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn('petugas_lapangan');
        });
    }

    public function down(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->string('petugas_lapangan')->nullable()->after('tanggal_eksekusi');
        });

        foreach (DB::table('pemangkasans')->get() as $row) {
            DB::table('pemangkasans')->where('id', $row->id)->update([
                'petugas_lapangan' => $row->pelaksana,
            ]);
        }

        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn(['asal_permohonan', 'pendukung_pelaksanaan', 'pelaksana', 'foto_sesudah']);
        });
    }
};
