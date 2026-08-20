<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taman_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taman_id')->constrained('tamans')->cascadeOnDelete();
            $table->string('path_foto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taman_images');
    }
};
