<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pemeliharaan_tamans', 'tampil_publik')) {
            Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
                $table->dropIndex(['tampil_publik']);
                $table->dropColumn('tampil_publik');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pemeliharaan_tamans', 'tampil_publik')) {
            Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
                $table->boolean('tampil_publik')->default(false)->after('keterangan');
                $table->index('tampil_publik');
            });
        }
    }
};
