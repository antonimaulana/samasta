<?php

namespace Tests\Feature;

use App\Models\AduanMasyarakat;
use App\Models\Pemangkasan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class P0SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_status_requires_kontak_verification(): void
    {
        AduanMasyarakat::create([
            'nomor_aduan' => 'ADU-20260717-0001',
            'lokasi' => 'Taman Test',
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Deskripsi aduan yang cukup panjang untuk validasi.',
            'nama_pelapor' => 'Pelapor',
            'kontak_pelapor' => '081234567890',
            'status' => 'Diproses',
            'catatan_admin' => 'Tim sedang menuju lokasi.',
        ]);

        $this->post(route('aduan.check.submit'), [
            'nomor_aduan' => 'ADU-20260717-0001',
            'kontak_verifikasi' => '7890',
        ])
            ->assertOk()
            ->assertSee('Diproses')
            ->assertSee('Tim sedang menuju lokasi.')
            ->assertDontSee('Pelapor');
    }

    public function test_check_status_rejects_wrong_kontak_verification(): void
    {
        AduanMasyarakat::create([
            'nomor_aduan' => 'ADU-20260717-0001',
            'lokasi' => 'Taman Test',
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Deskripsi aduan yang cukup panjang untuk validasi.',
            'nama_pelapor' => 'Pelapor',
            'kontak_pelapor' => '081234567890',
            'status' => 'Diproses',
        ]);

        $this->post(route('aduan.check.submit'), [
            'nomor_aduan' => 'ADU-20260717-0001',
            'kontak_verifikasi' => '0000',
        ])
            ->assertOk()
            ->assertSee('tidak ditemukan atau verifikasi')
            ->assertDontSee('Diproses');
    }

    public function test_check_status_rejects_honeypot(): void
    {
        $this->post(route('aduan.check.submit'), [
            'nomor_aduan' => 'ADU-20260717-0001',
            'kontak_verifikasi' => '7890',
            '_website' => 'https://spam.test',
        ])
            ->assertSessionHasErrors('_website');
    }

    public function test_viewer_cannot_update_pemangkasan_status(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);
        $pemangkasan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'lokasi_pohon' => 'Jl. Test',
            'asal' => 'Kecamatan Test',
            'tanggal_permohonan' => now(),
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => 'Kondisi pohon perlu pemangkasan.',
            'tanggal_eksekusi' => now()->addDay(),
            'status' => 'Rencana',
            'pelaksana' => ['Tim Wilayah 1'],
        ]);

        $this->actingAs($viewer)
            ->patch(route('admin.pemangkasans.update-status', $pemangkasan), [
                'status' => 'Diproses',
            ])
            ->assertForbidden();
    }

    public function test_viewer_cannot_mark_notifications_as_read(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->patch(route('admin.notifications.read', 'fake-id'))
            ->assertForbidden();
    }
}
