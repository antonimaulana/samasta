<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('survey_kepuasans')
            ->where('kategori', 'Portal SIMTAMAN')
            ->update(['kategori' => 'SIMTAMAN']);
    }

    public function down(): void
    {
        DB::table('survey_kepuasans')
            ->where('kategori', 'SIMTAMAN')
            ->update(['kategori' => 'Portal SIMTAMAN']);
    }
};
