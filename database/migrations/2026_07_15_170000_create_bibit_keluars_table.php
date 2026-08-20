<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bibit_keluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bibit_id')->constrained('bibits')->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->date('tanggal_keluar');
            $table->string('peruntukan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bibit_keluars');
    }
};
