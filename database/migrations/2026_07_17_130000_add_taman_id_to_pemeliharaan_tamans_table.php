<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            $table->foreignId('taman_id')->nullable()->after('tim')->constrained('tamans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemeliharaan_tamans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('taman_id');
        });
    }
};
