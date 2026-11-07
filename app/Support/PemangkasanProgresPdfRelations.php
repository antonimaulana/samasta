<?php

namespace App\Support;

use App\Models\PemangkasanProgres;
use Illuminate\Support\Facades\Schema;

final class PemangkasanProgresPdfRelations
{
    /**
     * @return list<string>
     */
    public static function names(): array
    {
        $relations = [];

        if (Schema::hasTable('petugas') && Schema::hasTable('pemangkasan_progres_petugas')) {
            $relations[] = 'petugas';
        }

        if (Schema::hasTable('pemangkasan_progres_armadas')) {
            $relations[] = 'armadas';

            if (ArmadaSchema::isReady()) {
                $relations[] = 'armadas.armada';
            }
        }

        return $relations;
    }

    /**
     * @param  iterable<int, PemangkasanProgres>  $entries
     */
    public static function loadForPdf(iterable $entries): void
    {
        $relations = self::names();

        if ($relations === []) {
            return;
        }

        foreach ($entries as $entry) {
            $entry->loadMissing($relations);
        }
    }

    public static function loadOneForPdf(PemangkasanProgres $entry): void
    {
        $relations = self::names();

        if ($relations !== []) {
            $entry->loadMissing($relations);
        }
    }
}
