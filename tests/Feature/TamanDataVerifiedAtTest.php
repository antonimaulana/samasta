<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanDataVerifiedAtTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_custom_data_verified_at_when_creating_taman(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $verifiedAt = '2026-03-15T09:30';

        $this->actingAs($admin)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Waktu Uji',
                'kategori' => 'Taman Kota',
                'luasan' => 1000,
                'alamat' => 'Alamat uji waktu',
                'deskripsi' => 'Deskripsi uji waktu pemutakhiran.',
                'data_verified_at' => $verifiedAt,
            ])
            ->assertRedirect(route('admin.tamans.index'));

        $taman = Taman::query()->where('nama_taman', 'Taman Waktu Uji')->first();

        $this->assertNotNull($taman);
        $this->assertSame(
            '2026-03-15 09:30:00',
            $taman->data_verified_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s')
        );
    }

    public function test_admin_can_update_data_verified_at_on_edit(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Edit Waktu',
            'kategori' => 'Taman Kota',
            'luasan' => 1200,
            'alamat' => 'Alamat edit waktu',
            'deskripsi' => 'Deskripsi edit waktu.',
            'data_verified_at' => now()->subMonths(2),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.tamans.update', $taman), [
                'nama_taman' => $taman->nama_taman,
                'kategori' => $taman->kategori,
                'luasan' => $taman->luasan,
                'alamat' => $taman->alamat,
                'deskripsi' => $taman->deskripsi,
                'data_verified_at' => '2026-01-10T14:45',
            ])
            ->assertRedirect(route('admin.tamans.index'));

        $taman->refresh();

        $this->assertSame(
            '2026-01-10 14:45:00',
            $taman->data_verified_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s')
        );
    }

    public function test_create_form_shows_datetime_input_for_data_verified_at(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.create'))
            ->assertOk()
            ->assertSee('name="data_verified_at"', false)
            ->assertSee('type="datetime-local"', false);
    }
}
