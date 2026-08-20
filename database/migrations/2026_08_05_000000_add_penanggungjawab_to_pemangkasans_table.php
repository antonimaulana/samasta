<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->string('penanggungjawab')->nullable()->after('asal');
            $table->string('kontak_permohonan')->nullable()->after('penanggungjawab');
        });
    }

    public function down(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn(['penanggungjawab', 'kontak_permohonan']);
        });
    }
};
