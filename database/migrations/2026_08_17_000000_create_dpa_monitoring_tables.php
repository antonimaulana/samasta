<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpa_tahun_anggarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('dpa_penyedias', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('pic')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('npwp')->nullable();
            $table->string('no_rekening')->nullable();
            $table->text('company_profile')->nullable();
            $table->string('company_profile_file')->nullable();
            $table->timestamps();
        });

        Schema::create('dpas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpa_tahun_anggaran_id')->constrained('dpa_tahun_anggarans')->cascadeOnDelete();
            $table->string('sub_kegiatan');
            $table->string('nomor_dpa')->nullable();
            $table->string('nama_dpa');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['dpa_tahun_anggaran_id', 'sub_kegiatan']);
        });

        Schema::create('dpa_paket_pekerjaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpa_id')->constrained('dpas')->cascadeOnDelete();
            $table->foreignId('dpa_penyedia_id')->nullable()->constrained('dpa_penyedias')->nullOnDelete();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('nama_paket');
            $table->unsignedBigInteger('pagu_anggaran')->default(0);
            $table->text('rincian_item_belanja')->nullable();
            $table->string('kode_rup')->nullable();
            $table->string('jenis_pengadaan')->nullable();
            $table->string('metode_pemilihan')->nullable();
            $table->unsignedBigInteger('anggaran_kas')->nullable();
            $table->string('masa_pelaksanaan')->nullable();
            $table->string('tahap')->default('pengadaan');
            $table->timestamps();

            $table->index(['dpa_id', 'tahap']);
        });

        Schema::create('dpa_paket_item_belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpa_paket_pekerjaan_id')->constrained('dpa_paket_pekerjaans')->cascadeOnDelete();
            $table->string('jenis_dokumen');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('uraian');
            $table->decimal('volume', 12, 2)->default(0);
            $table->string('satuan')->nullable();
            $table->unsignedBigInteger('harga_satuan')->default(0);
            $table->unsignedBigInteger('jumlah')->default(0);
            $table->timestamps();
        });

        Schema::create('dpa_document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('tahap');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('template_path')->nullable();
            $table->timestamps();
        });

        Schema::create('dpa_paket_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpa_paket_pekerjaan_id')->constrained('dpa_paket_pekerjaans')->cascadeOnDelete();
            $table->string('tahap');
            $table->string('kode_dokumen');
            $table->string('file_path')->nullable();
            $table->json('input_data')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['dpa_paket_pekerjaan_id', 'kode_dokumen'], 'dpa_paket_dokumen_unique');
        });

        Schema::create('dpa_paket_progres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpa_paket_pekerjaan_id')->constrained('dpa_paket_pekerjaans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('persentase')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('dokumentasi_path')->nullable();
            $table->timestamps();
        });

        Schema::create('dpa_paket_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpa_paket_pekerjaan_id')->constrained('dpa_paket_pekerjaans')->cascadeOnDelete();
            $table->string('judul');
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpa_paket_outputs');
        Schema::dropIfExists('dpa_paket_progres');
        Schema::dropIfExists('dpa_paket_dokumens');
        Schema::dropIfExists('dpa_document_templates');
        Schema::dropIfExists('dpa_paket_item_belanjas');
        Schema::dropIfExists('dpa_paket_pekerjaans');
        Schema::dropIfExists('dpas');
        Schema::dropIfExists('dpa_penyedias');
        Schema::dropIfExists('dpa_tahun_anggarans');
    }
};
