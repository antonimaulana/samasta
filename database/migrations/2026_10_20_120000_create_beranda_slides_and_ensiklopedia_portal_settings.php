<?php

use App\Support\BerandaSlideDefaults;
use App\Support\EnsiklopediaPortalConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beranda_slides', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('jenis', 20)->default('standard');
            $table->string('visual_mode', 20)->default('live');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('title_attr');
            $table->string('route_name')->nullable();
            $table->string('href')->nullable();
            $table->string('left_gradient')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('badge')->nullable();
            $table->string('hero_mode', 20)->default('single');
            $table->string('hero_live_preset', 40)->nullable();
            $table->string('hero_value')->nullable();
            $table->string('hero_label')->nullable();
            $table->string('hero_dual_left_value')->nullable();
            $table->string('hero_dual_left_label')->nullable();
            $table->string('hero_dual_right_value')->nullable();
            $table->string('hero_dual_right_label')->nullable();
            $table->boolean('show_stats')->default(true);
            $table->string('stat_left_value')->nullable();
            $table->string('stat_left_label')->nullable();
            $table->string('stat_right_value')->nullable();
            $table->string('stat_right_label')->nullable();
            $table->string('heading');
            $table->text('description');
            $table->json('tags')->nullable();
            $table->string('tag_classes')->nullable();
            $table->string('card_ring')->nullable();
            $table->string('card_shadow')->nullable();
            $table->string('cta_text');
            $table->string('cta_color')->nullable();
            $table->string('heading_hover')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('ensiklopedia_portal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_badge')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('search_placeholder')->nullable();
            $table->string('interaktif_title')->nullable();
            $table->string('interaktif_subtitle')->nullable();
            $table->string('quiz_button_label')->nullable();
            $table->json('interaktif_config')->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach (BerandaSlideDefaults::slides() as $slide) {
            DB::table('beranda_slides')->insert([
                ...$slide,
                'tags' => isset($slide['tags']) ? json_encode($slide['tags']) : null,
                'config' => isset($slide['config']) ? json_encode($slide['config']) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('ensiklopedia_portal_settings')->insert([
            'hero_badge' => '📚 Pusat Pengetahuan Hijau',
            'hero_title' => 'Ensiklopedia',
            'hero_description' => 'Pelajari program pemerintah, jenis tanaman, dan tips penghijauan — sumber edukasi resmi seputar pertamanan & RTH Kota Batam.',
            'search_placeholder' => 'Cari artikel, topik, atau kata kunci...',
            'interaktif_title' => 'Belajar Sambil Berinteraksi',
            'interaktif_subtitle' => 'Interaktif',
            'quiz_button_label' => '🧠 Mulai Kuis Tanaman',
            'interaktif_config' => json_encode(EnsiklopediaPortalConfig::interaktifDefaults()),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ensiklopedia_portal_settings');
        Schema::dropIfExists('beranda_slides');
    }
};
