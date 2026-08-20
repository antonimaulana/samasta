<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibits', function (Blueprint $table) {
            // Menghapus kolom lokasi_pembibitan
            if (Schema::hasColumn('bibits', 'lokasi_pembibitan')) {
                $table->dropColumn('lokasi_pembibitan');
            }
            // Menambah kolom sumber_bibit dengan pilihan (enum)
            $table->enum('sumber_bibit', ['Produksi', 'Pengadaan', 'Hibah'])->default('Produksi');
        });
    }

    public function down(): void
    {
        Schema::table('bibits', function (Blueprint $table) {
            $table->string('lokasi_pembibitan')->nullable();
            $table->dropColumn('sumber_bibit');
        });
    }
};