<?php

namespace Tests\Feature;

use App\Models\AduanMasyarakat;
use App\Models\Kelurahan;
use App\Models\PemeliharaanTaman;
use App\Models\Pemangkasan;
use App\Models\Taman;
use App\Models\TimPelaksana;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorWilayahScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_operator_wilayah_only_sees_tamans_in_assigned_kelurahan(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $team2 = TimPelaksana::where('nama', 'Tim Wilayah 2')->first();

        $kelurahanTeam1 = $team1->kelurahans()->first();
        $kelurahanTeam2 = $team2->kelurahans()->first();

        $tamanWilayah1 = Taman::create([
            'nama_taman' => 'Taman Wilayah 1',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam1->id,
            'luasan' => 500,
            'alamat' => 'Alamat wilayah 1',
            'deskripsi' => 'Deskripsi taman operator scope test wilayah 1.',
        ]);

        $tamanWilayah2 = Taman::create([
            'nama_taman' => 'Taman Wilayah 2',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam2->id,
            'luasan' => 600,
            'alamat' => 'Alamat wilayah 2',
            'deskripsi' => 'Deskripsi taman operator scope test wilayah 2.',
        ]);

        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.tamans.index'))
            ->assertOk()
            ->assertSee($tamanWilayah1->nama_taman)
            ->assertDontSee($tamanWilayah2->nama_taman);
    }

    public function test_operator_with_multiple_teams_sees_both_wilayah(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $team2 = TimPelaksana::where('nama', 'Tim Wilayah 2')->first();

        $tamanWilayah1 = Taman::create([
            'nama_taman' => 'Taman Multi W1',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $team1->kelurahans()->first()->id,
            'luasan' => 500,
            'alamat' => 'Alamat multi w1',
            'deskripsi' => 'Deskripsi operator multi wilayah test 1.',
        ]);

        $tamanWilayah2 = Taman::create([
            'nama_taman' => 'Taman Multi W2',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $team2->kelurahans()->first()->id,
            'luasan' => 600,
            'alamat' => 'Alamat multi w2',
            'deskripsi' => 'Deskripsi operator multi wilayah test 2.',
        ]);

        $tamanWilayah3 = Taman::create([
            'nama_taman' => 'Taman Multi W3',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => TimPelaksana::where('nama', 'Tim Wilayah 3')->first()->kelurahans()->first()->id,
            'luasan' => 700,
            'alamat' => 'Alamat multi w3',
            'deskripsi' => 'Deskripsi operator multi wilayah test 3.',
        ]);

        $operator = User::factory()->operatorTeams([$team1->id, $team2->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.tamans.index'))
            ->assertOk()
            ->assertSee($tamanWilayah1->nama_taman)
            ->assertSee($tamanWilayah2->nama_taman)
            ->assertDontSee($tamanWilayah3->nama_taman);
    }

    public function test_operator_with_all_wilayah_access_sees_everything(): void
    {
        $kelurahan = TimPelaksana::where('nama', 'Tim Wilayah 4')->first()->kelurahans()->first();

        $taman = Taman::create([
            'nama_taman' => 'Taman Semua Wilayah',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 800,
            'alamat' => 'Alamat semua wilayah',
            'deskripsi' => 'Deskripsi operator akses semua wilayah.',
        ]);

        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->get(route('admin.tamans.index'))
            ->assertOk()
            ->assertSee($taman->nama_taman);
    }

    public function test_operator_cannot_view_taman_outside_wilayah(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $kelurahanTeam2 = TimPelaksana::where('nama', 'Tim Wilayah 2')->first()->kelurahans()->first();

        $taman = Taman::create([
            'nama_taman' => 'Taman Luar Wilayah',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam2->id,
            'luasan' => 400,
            'alamat' => 'Alamat luar',
            'deskripsi' => 'Deskripsi taman di luar wilayah operator.',
        ]);

        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.tamans.show', $taman))
            ->assertForbidden();
    }

    public function test_operator_wilayah_only_sees_own_pemeliharaan_records(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $kelurahan = $team1->kelurahans()->first();

        $taman = Taman::create([
            'nama_taman' => 'Taman Pemeliharaan Scope',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 700,
            'alamat' => 'Alamat pemeliharaan scope',
            'deskripsi' => 'Deskripsi pemeliharaan operator scope test.',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 1',
            'taman_id' => $taman->id,
            'lokasi_pelaksanaan' => $taman->nama_taman,
            'foto_sebelum_1' => 'pemeliharaan-taman/test1.jpg',
            'foto_sebelum_2' => 'pemeliharaan-taman/test2.jpg',
            'foto_saat_1' => 'pemeliharaan-taman/test3.jpg',
            'foto_saat_2' => 'pemeliharaan-taman/test4.jpg',
            'foto_sesudah_1' => 'pemeliharaan-taman/test5.jpg',
            'foto_sesudah_2' => 'pemeliharaan-taman/test6.jpg',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 2',
            'lokasi_pelaksanaan' => 'Lokasi manual tim 2',
            'foto_sebelum_1' => 'pemeliharaan-taman/test1b.jpg',
            'foto_sebelum_2' => 'pemeliharaan-taman/test2b.jpg',
            'foto_saat_1' => 'pemeliharaan-taman/test3b.jpg',
            'foto_saat_2' => 'pemeliharaan-taman/test4b.jpg',
            'foto_sesudah_1' => 'pemeliharaan-taman/test5b.jpg',
            'foto_sesudah_2' => 'pemeliharaan-taman/test6b.jpg',
        ]);

        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.pemeliharaan-tamans.index', [
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($taman->nama_taman)
            ->assertDontSee('Lokasi manual tim 2');
    }

    public function test_operator_wilayah_only_sees_aduan_in_assigned_kelurahan(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $kelurahanTeam1 = $team1->kelurahans()->first();
        $kelurahanTeam2 = TimPelaksana::where('nama', 'Tim Wilayah 2')->first()->kelurahans()->first();

        $taman1 = Taman::create([
            'nama_taman' => 'Taman Aduan 1',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam1->id,
            'luasan' => 300,
            'alamat' => 'Alamat aduan 1',
            'deskripsi' => 'Deskripsi aduan operator scope test 1.',
        ]);

        $taman2 = Taman::create([
            'nama_taman' => 'Taman Aduan 2',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam2->id,
            'luasan' => 350,
            'alamat' => 'Alamat aduan 2',
            'deskripsi' => 'Deskripsi aduan operator scope test 2.',
        ]);

        AduanMasyarakat::create([
            'nomor_aduan' => 'ADU-W1-001',
            'taman_id' => $taman1->id,
            'lokasi' => $taman1->nama_taman,
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Aduan dalam wilayah operator.',
            'nama_pelapor' => 'Pelapor W1',
            'status' => 'Baru',
        ]);

        AduanMasyarakat::create([
            'nomor_aduan' => 'ADU-W2-001',
            'taman_id' => $taman2->id,
            'lokasi' => $taman2->nama_taman,
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Aduan di luar wilayah operator.',
            'nama_pelapor' => 'Pelapor W2',
            'status' => 'Baru',
        ]);

        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.aduan-masyarakats.index'))
            ->assertOk()
            ->assertSee('ADU-W1-001')
            ->assertDontSee('ADU-W2-001');
    }

    public function test_admin_user_form_requires_team_or_all_wilayah_for_operator(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Operator Tanpa Tim',
                'email' => 'operator-notim@example.com',
                'role' => User::ROLE_OPERATOR,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors('tim_pelaksana_ids');
    }

    public function test_admin_still_sees_all_tamans(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $kelurahan = Kelurahan::query()->first();

        Taman::create([
            'nama_taman' => 'Taman Admin View All',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 800,
            'alamat' => 'Alamat admin',
            'deskripsi' => 'Deskripsi admin melihat semua taman.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.index'))
            ->assertOk()
            ->assertSee('Taman Admin View All');
    }

    public function test_operator_pemangkasan_scoped_by_team_or_taman_wilayah(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $kelurahanTeam1 = $team1->kelurahans()->first();

        $tamanTeam1 = Taman::create([
            'nama_taman' => 'Taman Layanan W1',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam1->id,
            'luasan' => 500,
            'alamat' => 'Alamat layanan w1',
            'deskripsi' => 'Deskripsi layanan operator scope w1.',
        ]);

        Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $tamanTeam1->id,
            'lokasi_pohon' => $tamanTeam1->nama_taman,
            'asal' => 'Internal',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Instruksi',
            'kondisi_sebelum' => '',
            'dampak' => 'Rendah',
            'tanggal_eksekusi' => now()->toDateString(),
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Rencana',
        ]);

        Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'lokasi_pohon' => 'Lokasi Tim 2 Saja',
            'asal' => 'Internal',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Instruksi',
            'kondisi_sebelum' => '',
            'dampak' => 'Rendah',
            'tanggal_eksekusi' => now()->toDateString(),
            'pelaksana' => ['Tim Wilayah 2'],
            'status' => 'Rencana',
        ]);

        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.pemangkasans.index'))
            ->assertOk()
            ->assertSee($tamanTeam1->nama_taman)
            ->assertDontSee('Lokasi Tim 2 Saja');
    }

    public function test_operator_cannot_export_pemeliharaan_pdf_outside_team(): void
    {
        $otherRecord = PemeliharaanTaman::create([
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 2',
            'lokasi_pelaksanaan' => 'Lokasi export pdf tim 2',
            'foto_sebelum_1' => 'pemeliharaan-taman/test1b.jpg',
            'foto_sebelum_2' => 'pemeliharaan-taman/test2b.jpg',
            'foto_saat_1' => 'pemeliharaan-taman/test3b.jpg',
            'foto_saat_2' => 'pemeliharaan-taman/test4b.jpg',
            'foto_sesudah_1' => 'pemeliharaan-taman/test5b.jpg',
            'foto_sesudah_2' => 'pemeliharaan-taman/test6b.jpg',
        ]);

        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $this->actingAs($operator)
            ->get(route('admin.pemeliharaan-tamans.export-pdf-operasional', $otherRecord))
            ->assertForbidden();
    }
}
