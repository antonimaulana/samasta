<?php

namespace App\Http\Controllers;

use App\Support\EnsiklopediaInteraktif;
use App\Support\HomePageData;
use App\Support\PemerintahKotaBatam;
use App\Support\PenjagaHijauKota;
use App\Support\PublicRthStatisticsBuilder;
use App\Support\TamanUnggulanHero;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredTamans = HomePageData::featuredTamans();
        $stats = HomePageData::stats();
        $rthStats = app(PublicRthStatisticsBuilder::class)->build();

        return view('home', [
            ...$stats,
            'featuredTamans' => $featuredTamans,
            'heroFeaturedTamans' => TamanUnggulanHero::resolve(),
            'spotlightTaman' => $featuredTamans->first(),
            'rthTotalLuas' => $rthStats['totalLuasan'],
            'rthTotalLokasi' => $rthStats['totalTaman'],
            'rthKategoriCount' => count($rthStats['kategoriCards'] ?? []),
            'ensiklopediaKategoris' => HomePageData::ensiklopediaKategoris(),
            'faktaEdukasi' => EnsiklopediaInteraktif::faktaEdukasi(),
            'penjagaHijauGallery' => PenjagaHijauKota::gallery(),
            'pimpinanKota' => PemerintahKotaBatam::pimpinan(),
            'visiMisiKota' => PemerintahKotaBatam::visiMisi(),
            'totalKuisPertanyaan' => count(EnsiklopediaInteraktif::kuis()),
        ]);
    }
}
