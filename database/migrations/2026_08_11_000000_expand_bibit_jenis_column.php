<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (\App\Models\Bibit::LEGACY_JENIS_MAP as $legacy => $replacement) {
            DB::table('bibits')->where('jenis', $legacy)->update(['jenis' => $replacement]);
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE bibits MODIFY jenis VARCHAR(80) NOT NULL');

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('bibits_new', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tanaman');
            $table->string('nama_ilmiah', 150)->nullable();
            $table->string('jenis', 80);
            $table->unsignedInteger('stok_tersedia')->default(0);
            $table->enum('sumber_bibit', ['Produksi', 'Pengadaan', 'Hibah'])->default('Produksi');
            $table->boolean('status_siap_tanam')->default(false);
            $table->timestamps();
        });

        DB::statement('INSERT INTO bibits_new (id, nama_tanaman, nama_ilmiah, jenis, stok_tersedia, sumber_bibit, status_siap_tanam, created_at, updated_at)
            SELECT id, nama_tanaman, nama_ilmiah, jenis, stok_tersedia, sumber_bibit, status_siap_tanam, created_at, updated_at FROM bibits');

        Schema::drop('bibits');
        Schema::rename('bibits_new', 'bibits');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach (\App\Models\Bibit::LEGACY_JENIS_MAP as $legacy => $replacement) {
            DB::table('bibits')->where('jenis', $replacement)->update(['jenis' => $legacy]);
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bibits MODIFY jenis ENUM('Pohon', 'Tanaman Hias', 'Semak') NOT NULL");
        }
    }
};
