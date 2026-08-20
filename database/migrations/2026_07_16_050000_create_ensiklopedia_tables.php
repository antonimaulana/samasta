<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ensiklopedia_kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('icon', 10)->default('📚');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('ensiklopedia_artikels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ensiklopedia_kategori_id')->constrained('ensiklopedia_kategoris')->cascadeOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('icon', 10)->default('📄');
            $table->string('ringkas', 500);
            $table->text('konten');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ensiklopedia_artikels');
        Schema::dropIfExists('ensiklopedia_kategoris');
    }
};
