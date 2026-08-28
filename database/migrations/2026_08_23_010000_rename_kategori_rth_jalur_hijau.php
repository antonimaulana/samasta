<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tamans')
            ->where('kategori', 'RTH Jalur Hijau')
            ->update(['kategori' => 'Jalur Hijau Jalan']);
    }

    public function down(): void
    {
        DB::table('tamans')
            ->where('kategori', 'Jalur Hijau Jalan')
            ->update(['kategori' => 'RTH Jalur Hijau']);
    }
};
