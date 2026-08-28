<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemangkasan_armadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemangkasan_id')->constrained('pemangkasans')->cascadeOnDelete();
            $table->foreignId('alat_sarana_operasional_id')
                ->nullable()
                ->constrained('alat_sarana_operasionals')
                ->nullOnDelete();
            $table->string('jenis_armada');
            $table->string('no_plat')->default('');
            $table->string('sopir');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemangkasan_armadas');
    }
};
