<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taman_images', function (Blueprint $table) {
            $table->unsignedSmallInteger('urutan')->default(0)->after('path_foto');
        });

        $tamanIds = DB::table('taman_images')->distinct()->pluck('taman_id');

        foreach ($tamanIds as $tamanId) {
            $imageIds = DB::table('taman_images')
                ->where('taman_id', $tamanId)
                ->orderBy('id')
                ->pluck('id');

            foreach ($imageIds as $index => $imageId) {
                DB::table('taman_images')
                    ->where('id', $imageId)
                    ->update(['urutan' => ($index + 1) * 10]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('taman_images', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
