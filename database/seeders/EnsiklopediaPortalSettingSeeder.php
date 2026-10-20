<?php

namespace Database\Seeders;

use App\Models\EnsiklopediaPortalSetting;
use App\Support\EnsiklopediaPortalConfig;
use Illuminate\Database\Seeder;

class EnsiklopediaPortalSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (EnsiklopediaPortalSetting::query()->exists()) {
            return;
        }

        EnsiklopediaPortalSetting::query()->create([
            ...EnsiklopediaPortalConfig::heroFormDefaults(),
            'interaktif_config' => EnsiklopediaPortalConfig::interaktifDefaults(),
        ]);
    }
}
