<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->string('jenis_layanan')->default('Pemangkasan Pohon')->after('id');
            $table->text('dampak')->nullable()->after('kondisi_sebelum');
        });

        DB::table('pemangkasans')->update(['jenis_layanan' => 'Pemangkasan Pohon']);

        if (Schema::hasTable('pohon_tumbangs')) {
            foreach (DB::table('pohon_tumbangs')->get() as $row) {
                DB::table('pemangkasans')->insert([
                    'jenis_layanan' => 'Penanganan Pohon Tumbang',
                    'lokasi_pohon' => $row->lokasi_pohon,
                    'jenis_pohon' => $row->jenis_pohon,
                    'asal' => $row->asal,
                    'tanggal_permohonan' => $row->tanggal_laporan,
                    'kategori' => $row->kategori,
                    'kondisi_sebelum' => $row->kondisi_tumbang,
                    'dampak' => $row->dampak,
                    'pendukung_pelaksanaan' => $row->pendukung_pelaksanaan,
                    'tanggal_eksekusi' => $row->tanggal_penanganan,
                    'pelaksana' => $row->pelaksana,
                    'status' => $row->status,
                    'foto_sesudah' => $row->foto_sesudah,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::dropIfExists('pohon_tumbangs');
        }
    }

    public function down(): void
    {
        Schema::create('pohon_tumbangs', function (Blueprint $table) {
            $table->id();
            $table->string('lokasi_pohon');
            $table->string('jenis_pohon');
            $table->string('asal');
            $table->date('tanggal_laporan');
            $table->string('kategori');
            $table->text('kondisi_tumbang');
            $table->text('dampak')->nullable();
            $table->text('pendukung_pelaksanaan')->nullable();
            $table->date('tanggal_penanganan');
            $table->string('pelaksana');
            $table->enum('status', ['Rencana', 'Diproses', 'Selesai'])->default('Rencana');
            $table->string('foto_sesudah')->nullable();
            $table->timestamps();
        });

        foreach (DB::table('pemangkasans')->where('jenis_layanan', 'Penanganan Pohon Tumbang')->get() as $row) {
            DB::table('pohon_tumbangs')->insert([
                'lokasi_pohon' => $row->lokasi_pohon,
                'jenis_pohon' => $row->jenis_pohon,
                'asal' => $row->asal,
                'tanggal_laporan' => $row->tanggal_permohonan,
                'kategori' => $row->kategori,
                'kondisi_tumbang' => $row->kondisi_sebelum,
                'dampak' => $row->dampak,
                'pendukung_pelaksanaan' => $row->pendukung_pelaksanaan,
                'tanggal_penanganan' => $row->tanggal_eksekusi,
                'pelaksana' => $row->pelaksana,
                'status' => $row->status,
                'foto_sesudah' => $row->foto_sesudah,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        DB::table('pemangkasans')->where('jenis_layanan', 'Penanganan Pohon Tumbang')->delete();

        Schema::table('pemangkasans', function (Blueprint $table) {
            $table->dropColumn(['jenis_layanan', 'dampak']);
        });
    }
};
