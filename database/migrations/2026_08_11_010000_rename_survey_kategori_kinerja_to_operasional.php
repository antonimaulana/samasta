<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('survey_kepuasans')
            ->where('kategori', 'Kinerja Pertamanan')
            ->update(['kategori' => 'Operasional Pertamanan']);
    }

    public function down(): void
    {
        DB::table('survey_kepuasans')
            ->where('kategori', 'Operasional Pertamanan')
            ->update(['kategori' => 'Kinerja Pertamanan']);
    }
};
