<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aduan_masyarakats', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_aduan')->unique();
            $table->foreignId('taman_id')->nullable()->constrained('tamans')->nullOnDelete();
            $table->string('lokasi');
            $table->string('jenis_aduan');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('nama_pelapor');
            $table->string('kontak_pelapor')->nullable();
            $table->string('status')->default('Baru');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('jenis_aduan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aduan_masyarakats');
    }
};
