<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('armadas')) {
            return;
        }

        if (! Schema::hasTable('alat_sarana_operasionals')) {
            return;
        }

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->dropForeign(['alat_sarana_operasional_id']);
        });

        Schema::table('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->dropForeign(['alat_sarana_operasional_id']);
        });

        Schema::rename('alat_sarana_operasionals', 'armadas');

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->renameColumn('alat_sarana_operasional_id', 'armada_id');
        });

        Schema::table('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->renameColumn('alat_sarana_operasional_id', 'armada_id');
        });

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->foreign('armada_id')
                ->references('id')
                ->on('armadas')
                ->nullOnDelete();
        });

        Schema::table('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->foreign('armada_id')
                ->references('id')
                ->on('armadas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->dropForeign(['armada_id']);
        });

        Schema::table('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->dropForeign(['armada_id']);
        });

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->renameColumn('armada_id', 'alat_sarana_operasional_id');
        });

        Schema::table('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->renameColumn('armada_id', 'alat_sarana_operasional_id');
        });

        Schema::rename('armadas', 'alat_sarana_operasionals');

        Schema::table('pemeliharaan_taman_armadas', function (Blueprint $table) {
            $table->foreign('alat_sarana_operasional_id')
                ->references('id')
                ->on('alat_sarana_operasionals')
                ->nullOnDelete();
        });

        Schema::table('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->foreign('alat_sarana_operasional_id')
                ->references('id')
                ->on('alat_sarana_operasionals')
                ->nullOnDelete();
        });
    }
};
