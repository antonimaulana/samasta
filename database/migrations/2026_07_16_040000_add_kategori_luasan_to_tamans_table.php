<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table) {
            $table->string('kategori')->default('Taman Kota')->after('nama_taman');
            $table->unsignedInteger('luasan')->default(0)->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'luasan']);
        });
    }
};
