<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pemeliharaan_tamans', 'pemangkasan_progres'] as $table) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    $tanggal = (string) $row->tanggal;

                    if (! preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})\s+\d{2}:\d{2}:\d{2}$/', $tanggal, $matches)) {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([
                        'tanggal' => $matches[1].' '.$matches[2],
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        // Data cleanup is not reversible.
    }
};
