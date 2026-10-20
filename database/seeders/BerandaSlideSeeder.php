<?php

namespace Database\Seeders;

use App\Models\BerandaSlide;
use App\Support\BerandaSlideDefaults;
use Illuminate\Database\Seeder;

class BerandaSlideSeeder extends Seeder
{
    public function run(): void
    {
        if (BerandaSlide::query()->exists()) {
            return;
        }

        foreach (BerandaSlideDefaults::slides() as $slide) {
            BerandaSlide::query()->create($slide);
        }
    }
}
