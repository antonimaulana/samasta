<?php

namespace Tests\Feature;

use App\Models\Dpa;
use App\Models\DpaPaketItemBelanja;
use App\Models\DpaPaketPekerjaan;
use App\Models\DpaTahunAnggaran;
use App\Models\User;
use App\Support\DpaMonitoring;
use Database\Seeders\DpaDocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DpaMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DpaDocumentTemplateSeeder::class);
    }

    public function test_admin_can_access_dpa_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.dpa.dashboard'))
            ->assertOk()
            ->assertSee('Monitoring DPA');
    }

    public function test_operator_cannot_access_dpa_routes(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $this->actingAs($operator)
            ->get(route('admin.dpa.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_create_tahun_dpa_and_import_paket(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.dpa.tahun-anggarans.store'), [
                'tahun' => 2026,
                'keterangan' => 'APBD 2026',
            ])
            ->assertRedirect();

        $tahun = DpaTahunAnggaran::where('tahun', 2026)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.dpa.dpas.store'), [
                'dpa_tahun_anggaran_id' => $tahun->id,
                'sub_kegiatan' => DpaMonitoring::SUB_KEGIATAN[2]['slug'],
                'nama_dpa' => 'DPA RTH 2026',
            ])
            ->assertRedirect();

        $dpa = Dpa::where('nama_dpa', 'DPA RTH 2026')->firstOrFail();

        $csv = implode("\n", [
            implode(';', \App\Support\PaketPekerjaanCsvImporter::HEADERS),
            '5.1.02.04.01.0001;Belanja Barang;Paket Test;100000000;Honor;RUP-001;Jasa;Langsung;100000000;Jan-Des 2026',
        ]);

        $file = UploadedFile::fake()->createWithContent('paket.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.dpa.paket-pekerjaans.import.store', $dpa), ['file' => $file])
            ->assertRedirect(route('admin.dpa.dpas.show', $dpa))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('dpa_paket_pekerjaans', [
            'dpa_id' => $dpa->id,
            'nama_paket' => 'Paket Test',
            'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
        ]);
    }

    public function test_admin_can_update_paket_tahap(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $tahun = DpaTahunAnggaran::create(['tahun' => 2026]);
        $dpa = Dpa::create([
            'dpa_tahun_anggaran_id' => $tahun->id,
            'sub_kegiatan' => DpaMonitoring::SUB_KEGIATAN[0]['slug'],
            'nama_dpa' => 'DPA Test',
        ]);
        $paket = DpaPaketPekerjaan::create([
            'dpa_id' => $dpa->id,
            'nama_paket' => 'Paket A',
            'pagu_anggaran' => 50000000,
            'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.dpa.paket-pekerjaans.update-tahap', $paket), [
                'tahap' => DpaMonitoring::TAHAP_KONTRAK,
            ])
            ->assertRedirect();

        $this->assertSame(DpaMonitoring::TAHAP_KONTRAK, $paket->fresh()->tahap);
    }

    public function test_admin_can_generate_hps_pdf_from_form(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension required for PDF generation.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $tahun = DpaTahunAnggaran::create(['tahun' => 2026]);
        $dpa = Dpa::create([
            'dpa_tahun_anggaran_id' => $tahun->id,
            'sub_kegiatan' => DpaMonitoring::SUB_KEGIATAN[2]['slug'],
            'nama_dpa' => 'DPA RTH',
        ]);
        $paket = DpaPaketPekerjaan::create([
            'dpa_id' => $dpa->id,
            'nama_paket' => 'Paket HPS Test',
            'pagu_anggaran' => 100000000,
            'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
        ]);

        DpaPaketItemBelanja::create([
            'dpa_paket_pekerjaan_id' => $paket->id,
            'jenis_dokumen' => 'hps',
            'urutan' => 1,
            'uraian' => 'Honor pekerja',
            'volume' => 10,
            'satuan' => 'OH',
            'harga_satuan' => 150000,
            'jumlah' => 1500000,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.dpa.paket-pekerjaans.dokumens.generate', $paket), [
                'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
                'kode_dokumen' => 'hps',
                'fields' => [
                    'nomor' => 'HPS/001/2026',
                    'tanggal' => '2026-01-15',
                    'pejabat_nama' => 'Budi Santoso',
                    'pejabat_jabatan' => 'Kepala Bidang',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $dokumen = $paket->fresh()->dokumens()->where('kode_dokumen', 'hps')->first();

        $this->assertNotNull($dokumen);
        $this->assertNotNull($dokumen->file_path);
        $this->assertNotNull($dokumen->generated_at);
        $this->assertSame('HPS/001/2026', $dokumen->input_data['nomor'] ?? null);
        $relativePath = str_replace('storage/', '', $dokumen->file_path);
        $this->assertTrue(Storage::disk('public')->exists($relativePath));
    }

    public function test_admin_can_generate_usulan_pengadaan_pdf_matching_template(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension required for PDF generation.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $tahun = DpaTahunAnggaran::create(['tahun' => 2026]);
        $dpa = Dpa::create([
            'dpa_tahun_anggaran_id' => $tahun->id,
            'sub_kegiatan' => DpaMonitoring::SUB_KEGIATAN[2]['slug'],
            'nama_dpa' => 'DPA RTH',
        ]);
        $paket = DpaPaketPekerjaan::create([
            'dpa_id' => $dpa->id,
            'nomor_rekening' => '5.1.02.04.01.0001',
            'nama_paket' => 'Pemeliharaan Taman Kota',
            'pagu_anggaran' => 250000000,
            'kode_rup' => 'RUP-2026-001',
            'jenis_pengadaan' => 'Jasa Lainnya',
            'metode_pemilihan' => 'Pengadaan Langsung',
            'masa_pelaksanaan' => 'Januari - Desember 2026',
            'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.dpa.paket-pekerjaans.dokumens.generate', $paket), [
                'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
                'kode_dokumen' => 'usulan_pengadaan',
                'fields' => [
                    'nomor_permohonan' => '800.1.11.1/001/DPPK/2026',
                    'jumlah_lampiran' => 4,
                    'lokasi' => 'Kota Batam',
                    'jenis_kontrak' => 'Kontrak Tahun Berjalan',
                    'tanggal_permohonan' => '2026-01-20',
                    'nama_ppk' => 'Budi Santoso, S.T.',
                    'nip_ppk' => '198001012010011001',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $dokumen = $paket->fresh()->dokumens()->where('kode_dokumen', 'usulan_pengadaan')->first();

        $this->assertNotNull($dokumen);
        $this->assertSame('800.1.11.1/001/DPPK/2026', $dokumen->input_data['nomor_permohonan'] ?? null);
        $this->assertSame('Pemeliharaan Taman Kota', $dokumen->input_data['nama_pekerjaan'] ?? null);
        $this->assertSame(DpaMonitoring::KEGIATAN_UTAMA, $dokumen->input_data['nama_kegiatan'] ?? null);
        $this->assertSame('2026', $dokumen->input_data['tahun_anggaran'] ?? null);
    }
}
