<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alat_sarana_operasionals', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis');
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('peruntukan');
            $table->string('kondisi');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['peruntukan', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alat_sarana_operasionals');
    }
};
