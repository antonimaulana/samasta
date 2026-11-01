<?php

namespace App\Models\Concerns;

use App\Models\BibitStokBuktiFoto;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasBibitStokBuktiFotos
{
    public function buktiFotos(): MorphMany
    {
        return $this->morphMany(BibitStokBuktiFoto::class, 'photographable')->orderBy('urutan');
    }

    public function hasFotoBukti(): bool
    {
        if ($this->relationLoaded('buktiFotos')) {
            return $this->buktiFotos->isNotEmpty();
        }

        return $this->buktiFotos()->exists();
    }
}
