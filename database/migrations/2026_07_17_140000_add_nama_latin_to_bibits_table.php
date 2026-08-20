<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibits', function (Blueprint $table) {
            $table->string('nama_latin', 150)->nullable()->after('nama_tanaman');
        });
    }

    public function down(): void
    {
        Schema::table('bibits', function (Blueprint $table) {
            $table->dropColumn('nama_latin');
        });
    }
};
