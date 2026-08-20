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
            $table->string('asal')->nullable()->after('jenis_pohon');
            $table->date('tanggal_permohonan')->nullable()->after('asal');
            $table->enum('kategori', ['Instruksi', 'Laporan Masyarakat', 'Survei Lapangan'])
                ->default('Instruksi')
                ->after('tanggal_permohonan');
        });

        foreach (DB::table('pemangkasans')->get() as $row) {
            DB::table('pemangkasans')->where('id', $row->id)->update([
                'kategori' => $row->asal_permohonan ?? 'Instruksi',
                'tanggal_permohonan' => $row->tanggal_eksekusi,
                'asal' => $row->asal_permohonan ?? 'Instruksi',
            ]);
        }

        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn('asal_permohonan');
        });
    }

    public function down(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->enum('asal_permohonan', ['Instruksi', 'Laporan Masyarakat', 'Survei Lapangan'])
                ->default('Instruksi');
        });

        foreach (DB::table('pemangkasans')->get() as $row) {
            DB::table('pemangkasans')->where('id', $row->id)->update([
                'asal_permohonan' => $row->kategori,
            ]);
        }

        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn(['asal', 'tanggal_permohonan', 'kategori']);
        });
    }
};
