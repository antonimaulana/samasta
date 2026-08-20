<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pohon_tumbangs', function (Blueprint $table) {
            $table->id();
            $table->string('lokasi_pohon');
            $table->string('jenis_pohon');
            $table->string('asal');
            $table->date('tanggal_laporan');
            $table->string('kategori');
            $table->text('kondisi_tumbang');
            $table->text('dampak')->nullable();
            $table->text('pendukung_pelaksanaan')->nullable();
            $table->date('tanggal_penanganan');
            $table->string('pelaksana');
            $table->enum('status', ['Rencana', 'Diproses', 'Selesai'])->default('Rencana');
            $table->string('foto_sesudah')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pohon_tumbangs');
    }
};
