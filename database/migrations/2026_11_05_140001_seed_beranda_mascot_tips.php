<?php

use App\Models\BerandaMascotTip;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        BerandaMascotTip::seedDefaults();
    }

    public function down(): void
    {
        BerandaMascotTip::query()->whereIn('section_key', BerandaMascotTip::SECTION_KEYS)->delete();
    }
};
