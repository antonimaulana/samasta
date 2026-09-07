<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemangkasan_progres_armadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemangkasan_progres_id')
                ->constrained('pemangkasan_progres')
                ->cascadeOnDelete();
            $table->foreignId('alat_sarana_operasional_id')
                ->nullable()
                ->constrained('alat_sarana_operasionals')
                ->nullOnDelete();
            $table->string('jenis_armada');
            $table->string('no_plat')->default('');
            $table->string('sopir');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('pemangkasan_armadas')) {
            DB::table('pemangkasan_armadas')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $progresId = DB::table('pemangkasan_progres')
                        ->where('pemangkasan_id', $row->pemangkasan_id)
                        ->orderByDesc('tanggal')
                        ->orderByDesc('id')
                        ->value('id');

                    if (! $progresId) {
                        continue;
                    }

                    DB::table('pemangkasan_progres_armadas')->insert([
                        'pemangkasan_progres_id' => $progresId,
                        'alat_sarana_operasional_id' => $row->alat_sarana_operasional_id,
                        'jenis_armada' => $row->jenis_armada,
                        'no_plat' => $row->no_plat,
                        'sopir' => $row->sopir,
                        'urutan' => $row->urutan,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            });

            Schema::drop('pemangkasan_armadas');
        }
    }

    public function down(): void
    {
        Schema::create('pemangkasan_armadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemangkasan_id')->constrained('pemangkasans')->cascadeOnDelete();
            $table->foreignId('alat_sarana_operasional_id')
                ->nullable()
                ->constrained('alat_sarana_operasionals')
                ->nullOnDelete();
            $table->string('jenis_armada');
            $table->string('no_plat')->default('');
            $table->string('sopir');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('pemangkasan_progres_armadas');
    }
};
