<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pejabats', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('image_path')->nullable();
            $table->string('accent_bg')->default('bg-emerald-100');
            $table->string('accent_ring')->default('ring-emerald-200/60');
            $table->string('photo_class')->nullable();
            $table->string('photo_frame_class')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('rth_kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('luas')->default(0);
            $table->unsignedSmallInteger('lokasi')->default(0);
            $table->string('icon', 10)->default('🌳');
            $table->text('ringkas')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('kota_profiles', function (Blueprint $table) {
            $table->id();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kota_profiles');
        Schema::dropIfExists('rth_kategoris');
        Schema::dropIfExists('pejabats');
    }
};
