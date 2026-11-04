<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->string('tim')->nullable()->after('pemangkasan_id');
            $table->index(['pemangkasan_id', 'tim'], 'pemangkasan_progres_pemangkasan_tim_index');
        });

        DB::table('pemangkasan_progres')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    if (filled($row->tim)) {
                        continue;
                    }

                    $pelaksana = DB::table('pemangkasans')
                        ->where('id', $row->pemangkasan_id)
                        ->value('pelaksana');

                    $teams = is_string($pelaksana) ? json_decode($pelaksana, true) : null;
                    $tim = is_array($teams) && isset($teams[0]) ? (string) $teams[0] : null;

                    DB::table('pemangkasan_progres')
                        ->where('id', $row->id)
                        ->update(['tim' => $tim]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->dropIndex('pemangkasan_progres_pemangkasan_tim_index');
            $table->dropColumn('tim');
        });
    }
};
