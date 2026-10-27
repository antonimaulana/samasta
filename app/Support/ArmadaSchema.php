<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

final class ArmadaSchema
{
    public static function isReady(): bool
    {
        if (! Schema::hasTable('armadas')) {
            return false;
        }

        foreach (['pemeliharaan_taman_armadas', 'pemangkasan_progres_armadas'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'armada_id')) {
                return false;
            }
        }

        return true;
    }
}
