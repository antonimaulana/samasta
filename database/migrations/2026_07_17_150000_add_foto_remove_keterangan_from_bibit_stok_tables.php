<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('status_siap_tanam');
            $table->dropColumn('keterangan');
        });

        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('peruntukan');
            $table->dropColumn('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->text('keterangan')->nullable();
            $table->dropColumn('foto');
        });

        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->text('keterangan')->nullable();
            $table->dropColumn('foto');
        });
    }
};
