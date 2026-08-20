<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(WilayahBatamSeeder::class);
        $this->call(TimPelaksanaSeeder::class);
        $this->call(KontenBerandaSeeder::class);

        if (app()->environment('production')) {
            $this->command?->warn('DatabaseSeeder dilewati di production. Buat akun admin secara manual.');

            return;
        }

        $password = env('ADMIN_PASSWORD');

        if (! filled($password)) {
            $this->command?->warn('ADMIN_PASSWORD belum diset. Lewati pembuatan admin. Set di .env lalu jalankan seeder lagi.');

            return;
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD minimal 12 karakter.');
        }

        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@sitaman.batam')],
            [
                'name' => env('ADMIN_NAME', 'Administrator Samasta'),
                'password' => $password,
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
