<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AduanMasyarakatController as AdminAduanMasyarakatController;
use App\Http\Controllers\Admin\AlatSaranaOperasionalController;
use App\Http\Controllers\Admin\BibitController;
use App\Http\Controllers\Admin\BibitKeluarController;
use App\Http\Controllers\Admin\BibitLaporanController;
use App\Http\Controllers\Admin\BibitMasukController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Dpa\DashboardController as DpaDashboardController;
use App\Http\Controllers\Admin\Dpa\DocumentTemplateController as DpaDocumentTemplateController;
use App\Http\Controllers\Admin\Dpa\DpaController as AdminDpaController;
use App\Http\Controllers\Admin\Dpa\PaketPekerjaanController as DpaPaketPekerjaanController;
use App\Http\Controllers\Admin\Dpa\PenyediaController as DpaPenyediaController;
use App\Http\Controllers\Admin\Dpa\TahunAnggaranController as DpaTahunAnggaranController;
use App\Http\Controllers\Admin\EnsiklopediaArtikelController;
use App\Http\Controllers\Admin\EnsiklopediaKategoriController;
use App\Http\Controllers\Admin\EvaluasiArmadaController;
use App\Http\Controllers\Admin\EvaluasiKelengkapanDataController;
use App\Http\Controllers\Admin\EvaluasiKinerjaTimController;
use App\Http\Controllers\Admin\EvaluasiMasukanMasyarakatController;
use App\Http\Controllers\Admin\EvaluasiOperasionalPermohonanController;
use App\Http\Controllers\Admin\EvaluasiPemeliharaanController;
use App\Http\Controllers\Admin\EvaluasiRapKonsolidasiController;
use App\Http\Controllers\Admin\EvaluasiRthTerpeliharaController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperasionalPertamananLaporanController;
use App\Http\Controllers\Admin\PemangkasanController;
use App\Http\Controllers\Admin\PemeliharaanTamanController;
use App\Http\Controllers\Admin\PetugasController;
use App\Http\Controllers\Admin\SurveyKepuasanController as AdminSurveyKepuasanController;
use App\Http\Controllers\Admin\TamanController as AdminTamanController;
use App\Http\Controllers\Admin\TamanLaporanController;
use App\Http\Controllers\Admin\TimPelaksanaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RthArController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.access', 'admin.viewer.scope', 'admin.audit'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export-pdf');
    Route::get('tamans/resolve-wilayah', [AdminTamanController::class, 'resolveWilayah'])
        ->name('tamans.resolve-wilayah');
    Route::get('tamans/export/csv', [AdminTamanController::class, 'exportCsv'])
        ->name('tamans.export.csv');
    Route::get('tamans/import/template', [AdminTamanController::class, 'importTemplate'])
        ->name('tamans.import.template');
    Route::get('tamans/import', [AdminTamanController::class, 'importForm'])
        ->name('tamans.import');
    Route::post('tamans/import', [AdminTamanController::class, 'import'])
        ->name('tamans.import.store');
    Route::get('tamans/export/pdf', function (Request $request) {
        return redirect()->route('admin.taman-laporan.export-pdf', $request->query());
    })->name('tamans.export-pdf');
    Route::get('taman-laporan', [TamanLaporanController::class, 'index'])->name('taman-laporan.index');
    Route::get('taman-laporan/export/pdf', [TamanLaporanController::class, 'exportPdf'])->name('taman-laporan.export-pdf');
    Route::get('tamans/{taman}/ar-qr', [RthArController::class, 'qrImage'])
        ->name('tamans.ar-qr');
    Route::delete('tamans/{taman}/images/{image}', [AdminTamanController::class, 'destroyImage'])
        ->name('tamans.images.destroy');
    Route::resource('tamans', AdminTamanController::class);
    Route::get('bibits/import/template', [BibitController::class, 'importTemplate'])
        ->name('bibits.import.template');
    Route::get('bibits/import', [BibitController::class, 'importForm'])
        ->name('bibits.import');
    Route::post('bibits/import', [BibitController::class, 'import'])
        ->name('bibits.import.store');
    Route::resource('bibits', BibitController::class);
    Route::resource('bibit-keluars', BibitKeluarController::class)->names('bibit-keluars');
    Route::resource('bibit-masuks', BibitMasukController::class)->names('bibit-masuks');
    Route::get('bibit-laporan', [BibitLaporanController::class, 'index'])->name('bibit-laporan.index');
    Route::get('bibit-laporan/export/pdf', [BibitLaporanController::class, 'exportPdf'])->name('bibit-laporan.export-pdf');
    Route::get('pemangkasans/schedule-conflicts', [PemangkasanController::class, 'checkScheduleConflict'])
        ->name('pemangkasans.schedule-conflicts');
    Route::get('pemangkasans/{pemangkasan}/progres/{pemangkasanProgres}/export/pdf', [PemangkasanController::class, 'exportPdfProgres'])
        ->name('pemangkasans.export-pdf-progres');
    Route::get('pemangkasans/{pemangkasan}/progres/{pemangkasanProgres}/edit', [PemangkasanController::class, 'editProgres'])
        ->name('pemangkasans.progres.edit');
    Route::put('pemangkasans/{pemangkasan}/progres/{pemangkasanProgres}', [PemangkasanController::class, 'updateProgres'])
        ->name('pemangkasans.progres.update');
    Route::patch('pemangkasans/{pemangkasan}/status', [PemangkasanController::class, 'updateStatus'])
        ->name('pemangkasans.update-status');
    Route::resource('pemangkasans', PemangkasanController::class);
    Route::get('jadwal-layanan', function (Request $request) {
        return redirect()->route('admin.pemangkasans.index', array_filter([
            'view' => $request->input('view', 'hari_ini'),
            'pelaksana' => $request->input('pelaksana'),
            'jenis' => $request->input('jenis'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ]));
    })->name('jadwal-layanan.index');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('operasional-pertamanan-laporan', [OperasionalPertamananLaporanController::class, 'index'])
        ->name('operasional-pertamanan-laporan.index');
    Route::get('operasional-pertamanan-laporan/export/pdf', [OperasionalPertamananLaporanController::class, 'exportPdf'])
        ->name('operasional-pertamanan-laporan.export-pdf');
    Route::get('evaluasi/pemeliharaan', [EvaluasiPemeliharaanController::class, 'index'])
        ->name('evaluasi.pemeliharaan.index');
    Route::get('evaluasi/pemeliharaan/export/pdf', [EvaluasiPemeliharaanController::class, 'exportPdf'])
        ->name('evaluasi.pemeliharaan.export-pdf');
    Route::get('evaluasi/armada', [EvaluasiArmadaController::class, 'index'])
        ->name('evaluasi.armada.index');
    Route::get('evaluasi/armada/export/pdf', [EvaluasiArmadaController::class, 'exportPdf'])
        ->name('evaluasi.armada.export-pdf');
    Route::get('evaluasi/kinerja-tim', [EvaluasiKinerjaTimController::class, 'index'])
        ->name('evaluasi.kinerja-tim.index');
    Route::get('evaluasi/kinerja-tim/export/pdf', [EvaluasiKinerjaTimController::class, 'exportPdf'])
        ->name('evaluasi.kinerja-tim.export-pdf');
    Route::get('evaluasi/operasional-permohonan', [EvaluasiOperasionalPermohonanController::class, 'index'])
        ->name('evaluasi.operasional-permohonan.index');
    Route::get('evaluasi/operasional-permohonan/export/pdf', [EvaluasiOperasionalPermohonanController::class, 'exportPdf'])
        ->name('evaluasi.operasional-permohonan.export-pdf');
    Route::get('evaluasi/rth-terpelihara', [EvaluasiRthTerpeliharaController::class, 'index'])
        ->name('evaluasi.rth-terpelihara.index');
    Route::get('evaluasi/rth-terpelihara/export/pdf', [EvaluasiRthTerpeliharaController::class, 'exportPdf'])
        ->name('evaluasi.rth-terpelihara.export-pdf');
    Route::get('evaluasi/masukan-masyarakat', [EvaluasiMasukanMasyarakatController::class, 'index'])
        ->name('evaluasi.masukan-masyarakat.index');
    Route::get('evaluasi/masukan-masyarakat/export/pdf', [EvaluasiMasukanMasyarakatController::class, 'exportPdf'])
        ->name('evaluasi.masukan-masyarakat.export-pdf');
    Route::get('evaluasi/kelengkapan-data', [EvaluasiKelengkapanDataController::class, 'index'])
        ->name('evaluasi.kelengkapan-data.index');
    Route::get('evaluasi/kelengkapan-data/export/pdf', [EvaluasiKelengkapanDataController::class, 'exportPdf'])
        ->name('evaluasi.kelengkapan-data.export-pdf');
    Route::middleware('admin.feature:rap_konsolidasi')->group(function () {
        Route::get('evaluasi/rap-konsolidasi', [EvaluasiRapKonsolidasiController::class, 'index'])
            ->name('evaluasi.rap-konsolidasi.index');
        Route::get('evaluasi/rap-konsolidasi/export/pdf', [EvaluasiRapKonsolidasiController::class, 'exportPdf'])
            ->name('evaluasi.rap-konsolidasi.export-pdf');
    });
    Route::get('kinerja-pertamanan-laporan', function (Request $request) {
        return redirect()->route('admin.operasional-pertamanan-laporan.index', $request->query());
    })->name('kinerja-pertamanan-laporan.index');
    Route::get('kinerja-pertamanan-laporan/export/pdf', function (Request $request) {
        return redirect()->route('admin.operasional-pertamanan-laporan.export-pdf', $request->query());
    })->name('kinerja-pertamanan-laporan.export-pdf');
    Route::get('layanan-pertamanan-laporan', function (Request $request) {
        return redirect()->route('admin.operasional-pertamanan-laporan.index', $request->query());
    });
    Route::get('layanan-pertamanan-laporan/export/pdf', function (Request $request) {
        return redirect()->route('admin.operasional-pertamanan-laporan.export-pdf', $request->query());
    });
    Route::get('pemeliharaan-tamans/export/pdf', [PemeliharaanTamanController::class, 'exportPdf'])
        ->name('pemeliharaan-tamans.export-pdf');
    Route::get('pemeliharaan-tamans/{pemeliharaan_taman}/export/pdf', [PemeliharaanTamanController::class, 'exportPdfOperasional'])
        ->name('pemeliharaan-tamans.export-pdf-operasional');
    Route::resource('pemeliharaan-tamans', PemeliharaanTamanController::class)->except(['show']);
    Route::resource('alat-sarana-operasionals', AlatSaranaOperasionalController::class);
    Route::resource('aduan-masyarakats', AdminAduanMasyarakatController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::patch('aduan-masyarakats/{aduan_masyarakat}/status', [AdminAduanMasyarakatController::class, 'updateStatus'])
        ->name('aduan-masyarakats.update-status');
    Route::get('survey-kepuasan', [AdminSurveyKepuasanController::class, 'index'])->name('survey-kepuasan.index');
    Route::delete('survey-kepuasan/{survey_kepuasan}', [AdminSurveyKepuasanController::class, 'destroy'])->name('survey-kepuasan.destroy');

    Route::get('tim-pelaksanas/suggest', [TimPelaksanaController::class, 'suggest'])->name('tim-pelaksanas.suggest');
    Route::get('tim-pelaksanas/roster', [TimPelaksanaController::class, 'roster'])->name('tim-pelaksanas.roster');
    Route::resource('ensiklopedia-kategoris', EnsiklopediaKategoriController::class)->except(['show']);
    Route::resource('ensiklopedia-artikels', EnsiklopediaArtikelController::class)->except(['show']);

    Route::middleware('admin.manage')->group(function () {
        Route::get('tim-pelaksanas', [TimPelaksanaController::class, 'index'])->name('tim-pelaksanas.index');
        Route::get('tim-pelaksanas/{tim_pelaksana}/wilayah', [TimPelaksanaController::class, 'editWilayah'])->name('tim-pelaksanas.wilayah.edit');
        Route::put('tim-pelaksanas/{tim_pelaksana}/wilayah', [TimPelaksanaController::class, 'updateWilayah'])->name('tim-pelaksanas.wilayah.update');
        Route::get('tim-pelaksanas/{tim_pelaksana}/petugas', [PetugasController::class, 'index'])->name('tim-pelaksanas.petugas.index');
        Route::post('tim-pelaksanas/{tim_pelaksana}/petugas', [PetugasController::class, 'store'])->name('tim-pelaksanas.petugas.store');
        Route::put('tim-pelaksanas/{tim_pelaksana}/petugas/{petugas}', [PetugasController::class, 'update'])->name('tim-pelaksanas.petugas.update');
        Route::delete('tim-pelaksanas/{tim_pelaksana}/petugas/{petugas}', [PetugasController::class, 'destroy'])->name('tim-pelaksanas.petugas.destroy');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::resource('users', UserController::class)->except(['show']);

        Route::middleware('admin.feature:dpa_monitoring')->prefix('dpa')->name('dpa.')->group(function () {
            Route::get('/', [DpaDashboardController::class, 'index'])->name('dashboard');
            Route::get('tahun-anggarans/create', [DpaTahunAnggaranController::class, 'create'])->name('tahun-anggarans.create');
            Route::post('tahun-anggarans', [DpaTahunAnggaranController::class, 'store'])->name('tahun-anggarans.store');
            Route::get('tahun-anggarans/{tahun_anggaran}', [DpaTahunAnggaranController::class, 'show'])->name('tahun-anggarans.show');
            Route::get('tahun-anggarans/{tahun_anggaran}/kelola', [DpaTahunAnggaranController::class, 'kelola'])->name('tahun-anggarans.kelola');
            Route::get('paket-pekerjaans/import/template', [DpaPaketPekerjaanController::class, 'importTemplate'])->name('paket-pekerjaans.import.template');
            Route::get('dpas/{dpa}/paket-pekerjaans/import', [DpaPaketPekerjaanController::class, 'importForm'])->name('paket-pekerjaans.import');
            Route::post('dpas/{dpa}/paket-pekerjaans/import', [DpaPaketPekerjaanController::class, 'importStore'])->name('paket-pekerjaans.import.store');
            Route::get('dpas/{dpa}/paket-pekerjaans/create', [DpaPaketPekerjaanController::class, 'create'])->name('paket-pekerjaans.create');
            Route::post('dpas/{dpa}/paket-pekerjaans', [DpaPaketPekerjaanController::class, 'store'])->name('paket-pekerjaans.store');
            Route::resource('dpas', AdminDpaController::class)->except(['index']);
            Route::get('paket-pekerjaans/{paket_pekerjaan}', [DpaPaketPekerjaanController::class, 'show'])->name('paket-pekerjaans.show');
            Route::get('paket-pekerjaans/{paket_pekerjaan}/edit', [DpaPaketPekerjaanController::class, 'edit'])->name('paket-pekerjaans.edit');
            Route::put('paket-pekerjaans/{paket_pekerjaan}', [DpaPaketPekerjaanController::class, 'update'])->name('paket-pekerjaans.update');
            Route::delete('paket-pekerjaans/{paket_pekerjaan}', [DpaPaketPekerjaanController::class, 'destroy'])->name('paket-pekerjaans.destroy');
            Route::patch('paket-pekerjaans/{paket_pekerjaan}/tahap', [DpaPaketPekerjaanController::class, 'updateTahap'])->name('paket-pekerjaans.update-tahap');
            Route::post('paket-pekerjaans/{paket_pekerjaan}/dokumens/generate', [DpaPaketPekerjaanController::class, 'generateDokumen'])->name('paket-pekerjaans.dokumens.generate');
            Route::get('paket-pekerjaans/{paket_pekerjaan}/dokumens/preview', [DpaPaketPekerjaanController::class, 'previewDokumen'])->name('paket-pekerjaans.dokumens.preview');
            Route::post('paket-pekerjaans/{paket_pekerjaan}/dokumens', [DpaPaketPekerjaanController::class, 'storeDokumen'])->name('paket-pekerjaans.dokumens.store');
            Route::post('paket-pekerjaans/{paket_pekerjaan}/hps-items', [DpaPaketPekerjaanController::class, 'storeHpsItem'])->name('paket-pekerjaans.hps-items.store');
            Route::delete('paket-pekerjaans/{paket_pekerjaan}/hps-items/{item_belanja}', [DpaPaketPekerjaanController::class, 'destroyHpsItem'])->name('paket-pekerjaans.hps-items.destroy');
            Route::post('paket-pekerjaans/{paket_pekerjaan}/spk-items', [DpaPaketPekerjaanController::class, 'storeSpkItem'])->name('paket-pekerjaans.spk-items.store');
            Route::delete('paket-pekerjaans/{paket_pekerjaan}/spk-items/{item_belanja}', [DpaPaketPekerjaanController::class, 'destroySpkItem'])->name('paket-pekerjaans.spk-items.destroy');
            Route::post('paket-pekerjaans/{paket_pekerjaan}/progres', [DpaPaketPekerjaanController::class, 'storeProgres'])->name('paket-pekerjaans.progres.store');
            Route::post('paket-pekerjaans/{paket_pekerjaan}/outputs', [DpaPaketPekerjaanController::class, 'storeOutput'])->name('paket-pekerjaans.outputs.store');
            Route::resource('penyedias', DpaPenyediaController::class)->except(['show']);
            Route::get('document-templates', [DpaDocumentTemplateController::class, 'index'])->name('document-templates.index');
            Route::put('document-templates/{document_template}', [DpaDocumentTemplateController::class, 'update'])->name('document-templates.update');
        });
    });
});
