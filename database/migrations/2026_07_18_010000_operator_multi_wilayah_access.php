<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('akses_semua_wilayah')->default(false)->after('role');
        });

        Schema::create('tim_pelaksana_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tim_pelaksana_id')->constrained('tim_pelaksanas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'tim_pelaksana_id']);
        });

        if (Schema::hasColumn('users', 'tim_pelaksana_id')) {
            DB::table('users')
                ->whereNotNull('tim_pelaksana_id')
                ->orderBy('id')
                ->each(function ($user) {
                    DB::table('tim_pelaksana_user')->insert([
                        'user_id' => $user->id,
                        'tim_pelaksana_id' => $user->tim_pelaksana_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tim_pelaksana_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tim_pelaksana_id')
                ->nullable()
                ->after('role')
                ->constrained('tim_pelaksanas')
                ->nullOnDelete();
        });

        $firstTeamByUser = DB::table('tim_pelaksana_user')
            ->select('user_id', DB::raw('MIN(tim_pelaksana_id) as tim_pelaksana_id'))
            ->groupBy('user_id')
            ->get();

        foreach ($firstTeamByUser as $row) {
            DB::table('users')
                ->where('id', $row->user_id)
                ->update(['tim_pelaksana_id' => $row->tim_pelaksana_id]);
        }

        Schema::dropIfExists('tim_pelaksana_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('akses_semua_wilayah');
        });
    }
};
