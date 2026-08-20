<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibits', function (Blueprint $table) {
            $table->renameColumn('nama_latin', 'nama_ilmiah');
        });
    }

    public function down(): void
    {
        Schema::table('bibits', function (Blueprint $table) {
            $table->renameColumn('nama_ilmiah', 'nama_latin');
        });
    }
};
