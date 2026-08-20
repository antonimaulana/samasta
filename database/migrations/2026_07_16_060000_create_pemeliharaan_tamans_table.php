<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeliharaan_tamans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('tim');
            $table->string('lokasi_pelaksanaan');
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'tim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan_tamans');
    }
};
