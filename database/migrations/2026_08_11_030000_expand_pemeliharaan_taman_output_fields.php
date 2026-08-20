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
            $table->unsignedSmallInteger('jumlah_personil')->nullable()->after('lokasi_pelaksanaan');
            $table->unsignedSmallInteger('hari_ke')->nullable()->after('jumlah_personil');
            $table->unsignedSmallInteger('total_hari')->nullable()->after('hari_ke');
            $table->unsignedTinyInteger('persentase_progres')->nullable()->after('total_hari');
            $table->text('uraian_pekerjaan')->nullable()->after('persentase_progres');
        });

        if (Schema::hasColumn('pemeliharaan_tamans', 'keterangan')) {
            DB::table('pemeliharaan_tamans')
                ->whereNotNull('keterangan')
                ->orderBy('id')
                ->lazyById()
                ->each(function (object $row): void {
                    DB::table('pemeliharaan_tamans')
                        ->where('id', $row->id)
                        ->update(['uraian_pekerjaan' => $row->keterangan]);
                });

            Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }

        Schema::create('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemeliharaan_taman_id')->constrained('pemeliharaan_tamans')->cascadeOnDelete();
            $table->string('jenis_armada');
            $table->string('no_plat');
            $table->string('sopir');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan_taman_armadas');

        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('foto_sesudah_2');
        });

        DB::table('pemeliharaan_tamans')
            ->whereNotNull('uraian_pekerjaan')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row): void {
                DB::table('pemeliharaan_tamans')
                    ->where('id', $row->id)
                    ->update(['keterangan' => $row->uraian_pekerjaan]);
            });

        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_personil',
                'hari_ke',
                'total_hari',
                'persentase_progres',
                'uraian_pekerjaan',
            ]);
        });
    }
};
