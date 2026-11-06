<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->string('sumber')->nullable()->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->dropColumn('sumber');
        });
    }
};
