<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn('jenis_pohon');
        });
    }

    public function down(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->string('jenis_pohon')->default('—')->after('lokasi_pohon');
        });
    }
};
