<?php



namespace Tests\Feature;



use App\Models\Kelurahan;

use App\Models\PemeliharaanTaman;

use App\Models\Taman;

use App\Models\TimPelaksana;

use App\Models\User;

use Database\Seeders\TimPelaksanaSeeder;

use Database\Seeders\WilayahBatamSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Http\UploadedFile;

use Tests\TestCase;



class LapanganOperasionalTest extends TestCase

{

    use RefreshDatabase;



    protected function setUp(): void

    {

        parent::setUp();



        $this->seed(WilayahBatamSeeder::class);

        $this->seed(TimPelaksanaSeeder::class);



        config([

            'simapan.lapangan.pin' => '1234',

            'simapan.lapangan.session_ttl_minutes' => 480,

        ]);

    }



    public function test_guest_is_redirected_to_pin_page_when_lapangan_pin_enabled(): void

    {

        $this->get(route('lapangan.index'))

            ->assertRedirect(route('lapangan.unlock'));

    }



    public function test_guest_without_pin_config_redirects_to_login(): void

    {

        config(['simapan.lapangan.pin' => null]);



        $this->get(route('lapangan.index'))

            ->assertRedirect(route('login'));

    }



    public function test_guest_can_unlock_with_pin_and_access_menu(): void

    {

        $this->unlockLapangan();

        $this->get(route('lapangan.index'))

            ->assertOk()

            ->assertSee('Wilayah 1')

            ->assertSee('Permohonan')

            ->assertSee('Mode tanpa login');

    }



    public function test_wrong_pin_is_rejected(): void

    {

        $this->post(route('lapangan.unlock.store'), ['pin' => '9999'])

            ->assertSessionHasErrors('pin');



        $this->get(route('lapangan.index'))

            ->assertRedirect(route('lapangan.unlock'));

    }



    public function test_guest_can_store_pemeliharaan_without_login(): void

    {

        $this->unlockLapangan();



        $payload = [

            'tanggal' => now()->toDateString(),

            'lokasi_luar' => '1',

            'lokasi_pelaksanaan' => 'Jl. Guest Lapangan Test',

            'uraian_pekerjaan' => 'Pembersihan rutin tanpa login',

        ];



        foreach (array_keys(PemeliharaanTaman::FOTO_FIELDS) as $field) {

            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');

        }



        $this->post(route('lapangan.pemeliharaan.store', 'wilayah-1'), $payload)

            ->assertRedirect(route('lapangan.index'))

            ->assertSessionHas('success');



        $this->assertDatabaseHas('pemeliharaan_tamans', [

            'tim' => 'Tim Wilayah 1',

            'lokasi_pelaksanaan' => 'Jl. Guest Lapangan Test',

        ]);

    }



    public function test_viewer_cannot_access_lapangan(): void

    {

        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);



        $this->actingAs($viewer)

            ->get(route('lapangan.index'))

            ->assertForbidden();

    }



    public function test_operator_login_redirects_to_lapangan_menu(): void

    {

        $operator = User::factory()->create([

            'role' => User::ROLE_OPERATOR,

            'email' => 'operator-lapangan@test.local',

        ]);



        $this->post(route('login'), [

            'email' => 'operator-lapangan@test.local',

            'password' => 'password',

        ])->assertRedirect(route('lapangan.index'));

    }



    public function test_admin_login_still_redirects_to_admin_dashboard(): void

    {

        User::factory()->create([

            'role' => User::ROLE_ADMIN,

            'email' => 'admin-lapangan@test.local',

        ]);



        $this->post(route('login'), [

            'email' => 'admin-lapangan@test.local',

            'password' => 'password',

        ])->assertRedirect(route('admin.dashboard'));

    }



    public function test_admin_sees_all_lapangan_menu_items(): void

    {

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);



        $this->actingAs($admin)

            ->get(route('lapangan.index'))

            ->assertOk()

            ->assertSee('Wilayah 1')

            ->assertSee('Wilayah 4')

            ->assertSee('Nursery')

            ->assertSee('Armada')

            ->assertSee('Permohonan');

    }



    public function test_operator_menu_is_limited_to_assigned_team(): void

    {

        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->firstOrFail();

        $operator = User::factory()->operatorTeams([$team1->id])->create();



        $this->actingAs($operator)

            ->get(route('lapangan.index'))

            ->assertOk()

            ->assertSee('Wilayah 1')

            ->assertDontSee('Wilayah 2')

            ->assertDontSee('Nursery')

            ->assertSee('Permohonan');

    }



    public function test_operator_cannot_open_other_team_pemeliharaan_form(): void

    {

        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->firstOrFail();

        $operator = User::factory()->operatorTeams([$team1->id])->create();



        $this->actingAs($operator)

            ->get(route('lapangan.pemeliharaan.create', 'wilayah-2'))

            ->assertNotFound();

    }



    public function test_operator_can_store_pemeliharaan_for_own_team(): void

    {

        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->firstOrFail();

        $operator = User::factory()->operatorTeams([$team1->id])->create();



        $payload = [

            'tanggal' => now()->toDateString(),

            'lokasi_luar' => '1',

            'lokasi_pelaksanaan' => 'Jl. Input Lapangan Test',

            'uraian_pekerjaan' => 'Pembersihan rutin dari lapangan',

        ];



        foreach (array_keys(PemeliharaanTaman::FOTO_FIELDS) as $field) {

            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');

        }



        $this->actingAs($operator)

            ->post(route('lapangan.pemeliharaan.store', 'wilayah-1'), $payload)

            ->assertRedirect(route('lapangan.index'))

            ->assertSessionHas('success');



        $this->assertDatabaseHas('pemeliharaan_tamans', [

            'tim' => 'Tim Wilayah 1',

            'lokasi_pelaksanaan' => 'Jl. Input Lapangan Test',

            'uraian_pekerjaan' => 'Pembersihan rutin dari lapangan',

        ]);

    }



    public function test_operator_can_update_permohonan_progress_from_lapangan(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->firstOrFail();
        $operator = User::factory()->operatorTeams([$team1->id])->create();

        $taman = Taman::create([
            'nama_taman' => 'Taman Permohonan Lapangan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $team1->kelurahans()->first()->id ?? Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat permohonan lapangan',
            'deskripsi' => 'Deskripsi taman permohonan lapangan.',
        ]);

        $permohonan = \App\Models\Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => now()->toDateString(),
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Rencana',
        ]);

        $this->actingAs($operator)
            ->get(route('lapangan.permohonan.index'))
            ->assertOk()
            ->assertSee($taman->nama_taman)
            ->assertSee('Rencana')
            ->assertSee('Diproses')
            ->assertSee('Selesai');

        $this->actingAs($operator)
            ->get(route('lapangan.permohonan.edit', $permohonan))
            ->assertOk()
            ->assertSee('Mulai pekerjaan')
            ->assertDontSee('Konfirmasi pekerjaan selesai');

        $this->actingAs($operator)
            ->post(route('lapangan.permohonan.update', $permohonan), $this->progressPayload([
                'status' => 'Diproses',
            ]))
            ->assertRedirect(route('lapangan.permohonan.index'))
            ->assertSessionHas('success', 'Pekerjaan dimulai. Progres hari pertama berhasil disimpan.');

        $this->assertDatabaseHas('pemangkasans', [
            'id' => $permohonan->id,
            'status' => 'Diproses',
            'hari_tercapai' => 1,
            'persentase_progres' => 100,
        ]);

        $this->assertDatabaseHas('pemangkasan_progres', [
            'pemangkasan_id' => $permohonan->id,
            'jumlah_personil' => 5,
        ]);
    }

    public function test_guest_can_list_and_update_permohonan_progress(): void
    {
        $kelurahanId = Kelurahan::query()->value('id');

        $taman = Taman::create([
            'nama_taman' => 'Taman Guest Permohonan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 500,
            'alamat' => 'Alamat guest permohonan',
            'deskripsi' => 'Deskripsi guest permohonan.',
        ]);

        $permohonan = \App\Models\Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Dinas PU',
            'penanggungjawab' => 'Andi',
            'kontak_permohonan' => '081234567891',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Instruksi',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => now()->toDateString(),
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Rencana',
        ]);

        $this->unlockLapangan();

        $this->get(route('lapangan.permohonan.index'))
            ->assertOk()
            ->assertSee('Taman Guest Permohonan');

        $this->post(route('lapangan.permohonan.update', $permohonan), $this->progressPayload([
            'status' => 'Diproses',
        ]))
            ->assertRedirect(route('lapangan.permohonan.index'))
            ->assertSessionHas('success');

        $permohonan->refresh();

        $this->post(route('lapangan.permohonan.update', $permohonan), $this->progressPayload([
            'status' => 'Selesai',
            'tanggal_penyelesaian' => now()->toDateString(),
        ]))
            ->assertRedirect(route('lapangan.permohonan.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pemangkasans', [
            'id' => $permohonan->id,
            'status' => 'Selesai',
        ]);
    }

    public function test_lapangan_cannot_skip_from_rencana_to_selesai(): void
    {
        $kelurahanId = Kelurahan::query()->value('id');

        $taman = Taman::create([
            'nama_taman' => 'Taman Skip Status',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 500,
            'alamat' => 'Alamat skip status',
            'deskripsi' => 'Deskripsi skip status.',
        ]);

        $permohonan = \App\Models\Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => now()->toDateString(),
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Rencana',
        ]);

        $this->unlockLapangan();

        $this->post(route('lapangan.permohonan.update', $permohonan), $this->progressPayload([
            'status' => 'Selesai',
            'tanggal_penyelesaian' => now()->toDateString(),
        ]))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('pemangkasans', [
            'id' => $permohonan->id,
            'status' => 'Rencana',
        ]);
    }

    public function test_lapangan_permohonan_selesai_tab_lists_completed_items(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $taman = Taman::create([
            'nama_taman' => 'Taman Selesai Lapangan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 500,
            'alamat' => 'Alamat selesai',
            'deskripsi' => 'Deskripsi selesai lapangan.',
        ]);

        \App\Models\Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => now()->subDay()->toDateString(),
            'tanggal_penyelesaian' => now()->toDateString(),
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Selesai',
        ]);

        $this->actingAs($admin)
            ->get(route('lapangan.permohonan.index', ['status' => 'Selesai']))
            ->assertOk()
            ->assertSee('Taman Selesai Lapangan')
            ->assertDontSee('Isi progres');
    }

    public function test_legacy_permohonan_baru_url_redirects_to_index(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/lapangan/permohonan/baru')
            ->assertRedirect(route('lapangan.permohonan.index'));
    }

    public function test_lapangan_pemeliharaan_form_shows_team_scoped_locations(): void
    {
        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->firstOrFail();
        $kelurahanTeam1 = $team1->kelurahans()->firstOrFail();
        $kelurahanTeam2 = TimPelaksana::where('nama', 'Tim Wilayah 2')->firstOrFail()->kelurahans()->firstOrFail();

        $tamanWilayah1 = Taman::create([
            'nama_taman' => 'Taman Lapangan W1',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam1->id,
            'luasan' => 500,
            'alamat' => 'Alamat lapangan w1',
            'deskripsi' => 'Deskripsi taman lapangan wilayah 1.',
        ]);

        Taman::create([
            'nama_taman' => 'Taman Lapangan W2',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanTeam2->id,
            'luasan' => 600,
            'alamat' => 'Alamat lapangan w2',
            'deskripsi' => 'Deskripsi taman lapangan wilayah 2.',
        ]);

        $this->unlockLapangan();

        $this->get(route('lapangan.pemeliharaan.create', 'wilayah-1'))
            ->assertOk()
            ->assertSee('Lokasi Pelaksanaan')
            ->assertSee('Lokasi di luar wilayah pemeliharaan')
            ->assertSee($tamanWilayah1->nama_taman)
            ->assertSee('js/searchable-select.js', false)
            ->assertSee('data-searchable-select', false)
            ->assertDontSee('Taman Lapangan W2');
    }

    public function test_pemeliharaan_form_hides_duplicate_admin_actions(): void

    {

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);



        $this->actingAs($admin)

            ->get(route('lapangan.pemeliharaan.create', 'wilayah-1'))

            ->assertOk()

            ->assertSee('Simpan Pekerjaan', false)

            ->assertSee('Batal', false)

            ->assertDontSee('href="'.route('admin.pemeliharaan-tamans.index'), false);

    }



    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function progressPayload(array $overrides = []): array
    {
        return array_merge([
            'tanggal_progres' => now()->toDateString(),
            'jumlah_personil' => 5,
            'foto_sebelum' => UploadedFile::fake()->image('sebelum.jpg'),
            'foto_saat' => UploadedFile::fake()->image('saat.jpg'),
            'foto_sesudah' => UploadedFile::fake()->image('sesudah.jpg'),
        ], $overrides);
    }

    private function unlockLapangan(string $pin = '1234'): void
    {
        $this->post(route('lapangan.unlock.store'), ['pin' => $pin])
            ->assertRedirect(route('lapangan.index'));
    }

}


