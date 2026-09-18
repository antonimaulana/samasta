<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('petugas')) {
            Schema::create('petugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tim_pelaksana_id')->constrained('tim_pelaksanas')->cascadeOnDelete();
                $table->string('nama');
                $table->string('jabatan')->nullable();
                $table->boolean('is_inti')->default(false);
                $table->boolean('is_pengawas')->default(false);
                $table->boolean('aktif')->default(true);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();

                $table->unique(['tim_pelaksana_id', 'nama'], 'petugas_tim_nama_uq');
            });
        }

        if (! Schema::hasTable('pemeliharaan_taman_petugas')) {
            Schema::create('pemeliharaan_taman_petugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pemeliharaan_taman_id')->constrained('pemeliharaan_tamans')->cascadeOnDelete();
                $table->foreignId('petugas_id')->constrained('petugas')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['pemeliharaan_taman_id', 'petugas_id'], 'pmt_petugas_uq');
            });
        }

        if (! Schema::hasTable('pemangkasan_progres_petugas')) {
            Schema::create('pemangkasan_progres_petugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pemangkasan_progres_id')->constrained('pemangkasan_progres')->cascadeOnDelete();
                $table->foreignId('petugas_id')->constrained('petugas')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['pemangkasan_progres_id', 'petugas_id'], 'pp_progres_petugas_uq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pemangkasan_progres_petugas');
        Schema::dropIfExists('pemeliharaan_taman_petugas');
        Schema::dropIfExists('petugas');
    }
};
