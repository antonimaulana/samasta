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
            $table->date('tanggal_akhir_jadwal')->nullable()->after('tanggal_eksekusi');
            $table->unsignedSmallInteger('total_hari')->nullable()->after('tanggal_akhir_jadwal');
            $table->unsignedSmallInteger('hari_tercapai')->default(0)->after('total_hari');
            $table->unsignedTinyInteger('persentase_progres')->default(0)->after('hari_tercapai');
        });

        Schema::create('pemangkasan_progres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemangkasan_id')->constrained('pemangkasans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedSmallInteger('hari_ke');
            $table->unsignedSmallInteger('jumlah_personil');
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_saat')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pemangkasan_id', 'tanggal']);
        });

        DB::table('pemangkasans')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('pemangkasans')->where('id', $row->id)->update([
                    'tanggal_akhir_jadwal' => $row->tanggal_eksekusi,
                    'total_hari' => 1,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemangkasan_progres');

        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_akhir_jadwal',
                'total_hari',
                'hari_tercapai',
                'persentase_progres',
            ]);
        });
    }
};
