<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faq_settings')) {
            return;
        }

        DB::table('faq_settings')->update([
            'section_badge' => 'FAQ',
            'section_subtitle' => null,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // no-op
    }
};
