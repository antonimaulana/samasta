<?php

use App\Support\FaqDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_settings', function (Blueprint $table) {
            $table->id();
            $table->string('section_badge')->nullable();
            $table->string('section_title');
            $table->string('section_subtitle')->nullable();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('question');
            $table->text('answer');
            $table->timestamps();
        });

        $now = now();
        $settings = FaqDefaults::settings();
        DB::table('faq_settings')->insert([
            ...$settings,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (FaqDefaults::items() as $item) {
            DB::table('faqs')->insert([
                ...$item,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_settings');
    }
};
