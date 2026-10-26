<?php

use App\Models\BerandaSlide;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('beranda_slides', 'visual_mode')) {
            Schema::table('beranda_slides', function (Blueprint $table) {
                $table->string('visual_mode', 20)->default('live')->after('jenis');
            });
        }

        if (! Schema::hasColumn('beranda_slides', 'image_path')) {
            Schema::table('beranda_slides', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('left_gradient');
                $table->string('image_alt')->nullable()->after('image_path');
            });
        }

        DB::table('beranda_slides')->where('jenis', BerandaSlide::JENIS_SIMPONI)->update([
            'visual_mode' => BerandaSlide::VISUAL_ASSET,
        ]);

        DB::table('beranda_slides')->where('jenis', BerandaSlide::JENIS_PERDA)->update([
            'visual_mode' => BerandaSlide::VISUAL_TEMPLATE,
        ]);

        DB::table('beranda_slides')
            ->where('jenis', BerandaSlide::JENIS_STANDARD)
            ->whereNotNull('hero_live_preset')
            ->where('hero_live_preset', '!=', '')
            ->update(['visual_mode' => BerandaSlide::VISUAL_LIVE]);

        DB::table('beranda_slides')
            ->where('jenis', BerandaSlide::JENIS_STANDARD)
            ->where(function ($q) {
                $q->whereNull('hero_live_preset')->orWhere('hero_live_preset', '');
            })
            ->update(['visual_mode' => BerandaSlide::VISUAL_GRADIENT]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('beranda_slides', 'image_path')) {
            Schema::table('beranda_slides', function (Blueprint $table) {
                $table->dropColumn(['image_path', 'image_alt']);
            });
        }

        if (Schema::hasColumn('beranda_slides', 'visual_mode')) {
            Schema::table('beranda_slides', function (Blueprint $table) {
                $table->dropColumn('visual_mode');
            });
        }
    }
};
