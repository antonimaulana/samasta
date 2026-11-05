<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beranda_mascot_tips', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 40)->unique();
            $table->string('section_label', 120);
            $table->string('title', 160);
            $table->text('body');
            $table->string('image_asset', 255);
            $table->string('cta_label', 80)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beranda_mascot_tips');
    }
};
