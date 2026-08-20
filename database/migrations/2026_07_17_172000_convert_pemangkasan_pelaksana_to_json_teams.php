<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pemangkasans')
            ->whereNotNull('pelaksana')
            ->orderBy('id')
            ->each(function ($row) {
                $pelaksana = trim((string) $row->pelaksana);

                if ($pelaksana === '' || str_starts_with($pelaksana, '[')) {
                    return;
                }

                DB::table('pemangkasans')
                    ->where('id', $row->id)
                    ->update(['pelaksana' => json_encode([$pelaksana])]);
            });
    }

    public function down(): void
    {
        DB::table('pemangkasans')
            ->whereNotNull('pelaksana')
            ->orderBy('id')
            ->each(function ($row) {
                $decoded = json_decode((string) $row->pelaksana, true);

                if (! is_array($decoded) || $decoded === []) {
                    return;
                }

                DB::table('pemangkasans')
                    ->where('id', $row->id)
                    ->update(['pelaksana' => $decoded[0]]);
            });
    }
};
