<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bibit_masuk_batches', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal_masuk');
            $table->timestamps();
        });

        Schema::create('bibit_stok_bukti_fotos', function (Blueprint $table) {
            $table->id();
            $table->string('photographable_type');
            $table->unsignedBigInteger('photographable_id');
            $table->string('path');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['photographable_type', 'photographable_id'], 'bibit_stok_bukti_fotos_owner_index');
        });

        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->foreignId('bibit_masuk_batch_id')
                ->nullable()
                ->after('id')
                ->constrained('bibit_masuk_batches')
                ->cascadeOnDelete();
        });

        foreach (DB::table('bibit_masuks')->orderBy('id')->get() as $row) {
            $tanggal = $row->tanggal_masuk;
            if ($tanggal && strlen((string) $tanggal) <= 10) {
                $tanggal = $tanggal.' 08:00:00';
            }

            $batchId = DB::table('bibit_masuk_batches')->insertGetId([
                'tanggal_masuk' => $tanggal ?? now()->format('Y-m-d H:i:s'),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            DB::table('bibit_masuks')
                ->where('id', $row->id)
                ->update(['bibit_masuk_batch_id' => $batchId]);

            if (filled($row->foto)) {
                DB::table('bibit_stok_bukti_fotos')->insert([
                    'photographable_type' => 'App\\Models\\BibitMasukBatch',
                    'photographable_id' => $batchId,
                    'path' => $row->foto,
                    'urutan' => 0,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->dropColumn(['tanggal_masuk', 'foto']);
        });

        Schema::table('bibit_keluar_batches', function (Blueprint $table) {
            $table->string('dokumen_pendukung')->nullable()->after('foto');
        });

        foreach (DB::table('bibit_keluar_batches')->whereNotNull('foto')->where('foto', '!=', '')->get() as $batch) {
            DB::table('bibit_stok_bukti_fotos')->insert([
                'photographable_type' => 'App\\Models\\BibitKeluarBatch',
                'photographable_id' => $batch->id,
                'path' => $batch->foto,
                'urutan' => 0,
                'created_at' => $batch->created_at,
                'updated_at' => $batch->updated_at,
            ]);
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('bibit_keluar_batches', function (Blueprint $table) {
                $table->dateTime('tanggal_keluar_new')->nullable()->after('id');
            });

            foreach (DB::table('bibit_keluar_batches')->get() as $batch) {
                $tanggal = $batch->tanggal_keluar;
                if ($tanggal && strlen((string) $tanggal) <= 10) {
                    $tanggal = $tanggal.' 08:00:00';
                }

                DB::table('bibit_keluar_batches')
                    ->where('id', $batch->id)
                    ->update(['tanggal_keluar_new' => $tanggal]);
            }

            Schema::table('bibit_keluar_batches', function (Blueprint $table) {
                $table->dropColumn('tanggal_keluar');
            });

            Schema::table('bibit_keluar_batches', function (Blueprint $table) {
                $table->renameColumn('tanggal_keluar_new', 'tanggal_keluar');
            });
        } else {
            DB::statement('UPDATE bibit_keluar_batches SET tanggal_keluar = CONCAT(DATE(tanggal_keluar), " 08:00:00") WHERE CHAR_LENGTH(tanggal_keluar) <= 10 OR tanggal_keluar NOT LIKE "%:%"');

            Schema::table('bibit_keluar_batches', function (Blueprint $table) {
                $table->dateTime('tanggal_keluar')->change();
            });
        }

        Schema::table('bibit_keluar_batches', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }

    public function down(): void
    {
        Schema::table('bibit_keluar_batches', function (Blueprint $table) {
            $table->string('foto')->nullable();
        });

        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->date('tanggal_masuk')->nullable();
            $table->string('foto')->nullable();
        });

        foreach (DB::table('bibit_masuk_batches')->orderBy('id')->get() as $batch) {
            $foto = DB::table('bibit_stok_bukti_fotos')
                ->where('photographable_type', 'App\\Models\\BibitMasukBatch')
                ->where('photographable_id', $batch->id)
                ->orderBy('urutan')
                ->value('path');

            DB::table('bibit_masuks')
                ->where('bibit_masuk_batch_id', $batch->id)
                ->update([
                    'tanggal_masuk' => substr((string) $batch->tanggal_masuk, 0, 10),
                    'foto' => $foto,
                ]);
        }

        Schema::table('bibit_masuks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bibit_masuk_batch_id');
        });

        Schema::table('bibit_keluar_batches', function (Blueprint $table) {
            $table->dropColumn('dokumen_pendukung');
        });

        Schema::dropIfExists('bibit_stok_bukti_fotos');
        Schema::dropIfExists('bibit_masuk_batches');
    }
};
