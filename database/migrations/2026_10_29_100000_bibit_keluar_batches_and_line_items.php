<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bibit_keluar_batches', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_keluar');
            $table->string('peruntukan');
            $table->foreignId('taman_id')->nullable()->constrained('tamans')->nullOnDelete();
            $table->string('lokasi')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->foreignId('bibit_keluar_batch_id')
                ->nullable()
                ->after('id')
                ->constrained('bibit_keluar_batches')
                ->cascadeOnDelete();
        });

        foreach (DB::table('bibit_keluars')->orderBy('id')->get() as $row) {
            $batchId = DB::table('bibit_keluar_batches')->insertGetId([
                'tanggal_keluar' => $row->tanggal_keluar,
                'peruntukan' => $row->peruntukan,
                'taman_id' => $row->taman_id,
                'lokasi' => $row->lokasi,
                'foto' => $row->foto,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            DB::table('bibit_keluars')
                ->where('id', $row->id)
                ->update(['bibit_keluar_batch_id' => $batchId]);
        }

        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->dropForeign(['taman_id']);
            $table->dropColumn(['tanggal_keluar', 'peruntukan', 'taman_id', 'lokasi', 'foto']);
        });

    }

    public function down(): void
    {
        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->date('tanggal_keluar')->nullable();
            $table->string('peruntukan')->nullable();
            $table->foreignId('taman_id')->nullable()->constrained('tamans')->nullOnDelete();
            $table->string('lokasi')->nullable();
            $table->string('foto')->nullable();
        });

        foreach (DB::table('bibit_keluars')->whereNotNull('bibit_keluar_batch_id')->orderBy('id')->get() as $row) {
            $batch = DB::table('bibit_keluar_batches')->where('id', $row->bibit_keluar_batch_id)->first();
            if (! $batch) {
                continue;
            }

            DB::table('bibit_keluars')->where('id', $row->id)->update([
                'tanggal_keluar' => $batch->tanggal_keluar,
                'peruntukan' => $batch->peruntukan,
                'taman_id' => $batch->taman_id,
                'lokasi' => $batch->lokasi,
                'foto' => $batch->foto,
            ]);
        }

        Schema::table('bibit_keluars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bibit_keluar_batch_id');
        });

        Schema::dropIfExists('bibit_keluar_batches');
    }
};
