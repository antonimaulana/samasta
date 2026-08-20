<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->unsignedInteger('sisa_stok')->default(0)->after('jumlah');
            $table->boolean('status_siap_tanam')->default(false)->after('sumber');
        });

        DB::table('bibit_masuks')->update([
            'sisa_stok' => DB::raw('jumlah'),
        ]);
    }

    public function down(): void
    {
        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->dropColumn(['sisa_stok', 'status_siap_tanam']);
        });
    }
};
