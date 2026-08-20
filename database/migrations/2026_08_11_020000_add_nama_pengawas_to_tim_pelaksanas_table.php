<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private const PENGAWAS_BY_TEAM = [
        'Tim Wilayah 1' => 'Maryono',
        'Tim Wilayah 2' => 'Muhammad Rifan',
        'Tim Wilayah 3' => 'Syaiful',
        'Tim Wilayah 4' => 'Abdul Setio',
        'Tim Armada' => 'Munasir',
        'Tim Nursery' => 'Marsis',
    ];

    public function up(): void
    {
        Schema::table('tim_pelaksanas', function (Blueprint $table) {
            $table->string('nama_pengawas')->nullable()->after('nama');
        });

        foreach (self::PENGAWAS_BY_TEAM as $nama => $pengawas) {
            DB::table('tim_pelaksanas')
                ->where('nama', $nama)
                ->update(['nama_pengawas' => $pengawas]);
        }
    }

    public function down(): void
    {
        Schema::table('tim_pelaksanas', function (Blueprint $table) {
            $table->dropColumn('nama_pengawas');
        });
    }
};
