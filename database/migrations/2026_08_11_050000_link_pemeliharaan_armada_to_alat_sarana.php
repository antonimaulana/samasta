<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat_sarana_operasionals', function (Blueprint $table) {
            $table->string('no_plat')->nullable()->after('jenis');
            $table->string('sopir')->nullable()->after('no_plat');
        });

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->foreignId('alat_sarana_operasional_id')
                ->nullable()
                ->after('pemeliharaan_taman_id')
                ->constrained('alat_sarana_operasionals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alat_sarana_operasional_id');
        });

        Schema::table('alat_sarana_operasionals', function (Blueprint $table) {
            $table->dropColumn(['no_plat', 'sopir']);
        });
    }
};
