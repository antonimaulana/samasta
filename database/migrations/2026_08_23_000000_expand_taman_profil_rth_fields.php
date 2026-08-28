<?php

use App\Models\Taman;
use App\Support\TamanCompleteness;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun_pembangunan')->nullable()->after('fasilitas');
            $table->unsignedBigInteger('nilai_pembangunan')->nullable()->after('tahun_pembangunan');
            $table->string('kontraktor')->nullable()->after('nilai_pembangunan');
            $table->string('konsultan_perencana')->nullable()->after('kontraktor');
            $table->timestamp('data_verified_at')->nullable()->after('konsultan_perencana');
            $table->unsignedTinyInteger('kelengkapan_skor')->default(0)->after('data_verified_at');
            $table->string('kelengkapan_label', 10)->default('merah')->after('kelengkapan_skor');
        });

        $completeness = app(TamanCompleteness::class);

        Taman::query()->with('images')->chunkById(100, function ($tamans) use ($completeness) {
            foreach ($tamans as $taman) {
                $taman->fasilitas = Taman::normalizeFasilitasArray($taman->fasilitas);
                $taman->kelengkapan_skor = $completeness->score($taman);
                $taman->kelengkapan_label = $completeness->labelFromScore($taman->kelengkapan_skor);
                $taman->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table) {
            $table->dropColumn([
                'tahun_pembangunan',
                'nilai_pembangunan',
                'kontraktor',
                'konsultan_perencana',
                'data_verified_at',
                'kelengkapan_skor',
                'kelengkapan_label',
            ]);
        });
    }
};
