<?php

use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->string('lokasi')->nullable()->after('taman_id');
        });

        Taman::query()
            ->select(['id', 'nama_taman', 'alamat'])
            ->get()
            ->each(function (Taman $taman): void {
                $label = PemeliharaanTaman::lokasiLabelFromTaman($taman);

                DB::table('bibit_keluars')
                    ->where('taman_id', $taman->id)
                    ->whereNull('lokasi')
                    ->update(['lokasi' => $label]);
            });
    }

    public function down(): void
    {
        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }
};
