<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode_kemendagri', 20)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('kelurahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();

            $table->unique(['kecamatan_id', 'nama']);
        });

        Schema::table('tamans', function (Blueprint $table) {
            $table->foreignId('kelurahan_id')
                ->nullable()
                ->after('kategori')
                ->constrained('kelurahans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelurahan_id');
        });

        Schema::dropIfExists('kelurahans');
        Schema::dropIfExists('kecamatans');
    }
};
