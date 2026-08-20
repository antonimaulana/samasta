<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tim_pelaksanas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->boolean('memiliki_wilayah_kerja')->default(true);
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('kelurahan_tim_pelaksana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tim_pelaksana_id')->constrained('tim_pelaksanas')->cascadeOnDelete();
            $table->foreignId('kelurahan_id')->constrained('kelurahans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kelurahan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahan_tim_pelaksana');
        Schema::dropIfExists('tim_pelaksanas');
    }
};
