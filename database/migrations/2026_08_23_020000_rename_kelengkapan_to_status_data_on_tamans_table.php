<?php

use App\Models\Taman;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tamans', 'status_data')) {
            Schema::table('tamans', function (Blueprint $table) {
                $table->string('status_data', 20)
                    ->default(Taman::STATUS_DATA_BELUM_LENGKAP)
                    ->after('data_verified_at');
            });
        }

        if (Schema::hasColumn('tamans', 'kelengkapan_label')) {
            DB::table('tamans')
                ->where('kelengkapan_label', 'hijau')
                ->update(['status_data' => Taman::STATUS_DATA_LENGKAP]);

            DB::table('tamans')
                ->whereIn('kelengkapan_label', ['kuning', 'merah'])
                ->update(['status_data' => Taman::STATUS_DATA_BELUM_LENGKAP]);
        }

        if (Schema::hasColumn('tamans', 'kelengkapan_skor')) {
            DB::table('tamans')
                ->where('kelengkapan_skor', 100)
                ->update(['status_data' => Taman::STATUS_DATA_LENGKAP]);

            DB::table('tamans')
                ->where('kelengkapan_skor', '<', 100)
                ->update(['status_data' => Taman::STATUS_DATA_BELUM_LENGKAP]);
        }

        Schema::table('tamans', function (Blueprint $table) {
            if (Schema::hasColumn('tamans', 'kelengkapan_skor')) {
                $table->dropColumn('kelengkapan_skor');
            }

            if (Schema::hasColumn('tamans', 'kelengkapan_label')) {
                $table->dropColumn('kelengkapan_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table) {
            if (! Schema::hasColumn('tamans', 'kelengkapan_skor')) {
                $table->unsignedTinyInteger('kelengkapan_skor')->default(0)->after('data_verified_at');
            }

            if (! Schema::hasColumn('tamans', 'kelengkapan_label')) {
                $table->string('kelengkapan_label', 10)->default('merah')->after('kelengkapan_skor');
            }
        });

        if (Schema::hasColumn('tamans', 'status_data')) {
            DB::table('tamans')
                ->where('status_data', Taman::STATUS_DATA_LENGKAP)
                ->update([
                    'kelengkapan_label' => 'hijau',
                    'kelengkapan_skor' => 100,
                ]);

            DB::table('tamans')
                ->where('status_data', Taman::STATUS_DATA_BELUM_LENGKAP)
                ->update([
                    'kelengkapan_label' => 'merah',
                    'kelengkapan_skor' => 0,
                ]);

            Schema::table('tamans', function (Blueprint $table) {
                $table->dropColumn('status_data');
            });
        }
    }
};
