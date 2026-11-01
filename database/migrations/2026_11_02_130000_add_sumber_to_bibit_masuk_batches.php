<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bibit_masuk_batches', function (Blueprint $table) {
            $table->string('sumber')->nullable()->after('tanggal_masuk');
            $table->boolean('status_siap_tanam')->default(false)->after('sumber');
        });

        foreach (DB::table('bibit_masuk_batches')->orderBy('id')->get() as $batch) {
            $line = DB::table('bibit_masuks')
                ->where('bibit_masuk_batch_id', $batch->id)
                ->orderBy('id')
                ->first();

            if (! $line) {
                continue;
            }

            DB::table('bibit_masuk_batches')
                ->where('id', $batch->id)
                ->update([
                    'sumber' => $line->sumber,
                    'status_siap_tanam' => (bool) $line->status_siap_tanam,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('bibit_masuk_batches', function (Blueprint $table) {
            $table->dropColumn(['sumber', 'status_siap_tanam']);
        });
    }
};
