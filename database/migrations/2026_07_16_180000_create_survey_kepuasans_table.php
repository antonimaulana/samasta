<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_kepuasans', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');
            $table->unsignedTinyInteger('rating');
            $table->foreignId('taman_id')->nullable()->constrained('tamans')->nullOnDelete();
            $table->text('saran')->nullable();
            $table->string('nama')->nullable();
            $table->timestamps();

            $table->index(['kategori', 'created_at']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_kepuasans');
    }
};
