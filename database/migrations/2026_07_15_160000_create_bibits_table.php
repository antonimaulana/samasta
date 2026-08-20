<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bibits', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tanaman');
            $table->enum('jenis', ['Pohon', 'Tanaman Hias', 'Semak']);
            $table->unsignedInteger('stok_tersedia')->default(0);
            $table->string('lokasi_pembibitan');
            $table->boolean('status_siap_tanam')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bibits');
    }
};
