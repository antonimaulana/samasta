<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemangkasans', function (Blueprint $table) {
            $table->id();
            $table->string('lokasi_pohon');
            $table->string('jenis_pohon');
            $table->text('kondisi_sebelum');
            $table->date('tanggal_eksekusi');
            $table->string('petugas_lapangan');
            $table->enum('status', ['Rencana', 'Diproses', 'Selesai'])->default('Rencana');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemangkasans');
    }
};
