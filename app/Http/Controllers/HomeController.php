<?php

namespace App\Http\Controllers;

use App\Support\EnsiklopediaInteraktif;
use App\Support\HomePageData;
use App\Support\PemerintahKotaBatam;
use App\Support\PenjagaHijauKota;
use App\Support\RthKotaBatam;
use App\Support\TamanUnggulanHero;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredTamans = HomePageData::featuredTamans();
        $stats = HomePageData::stats();
        $rthTotals = RthKotaBatam::total();

        return view('home', [
            ...$stats,
            'featuredTamans' => $featuredTamans,
            'heroFeaturedTamans' => TamanUnggulanHero::resolve(),
            'spotlightTaman' => $featuredTamans->first(),
            'rthTotalLuas' => $rthTotals['luas'],
            'rthTotalLokasi' => $rthTotals['lokasi'],
            'ensiklopediaKategoris' => HomePageData::ensiklopediaKategoris(),
            'faktaEdukasi' => EnsiklopediaInteraktif::faktaEdukasi(),
            'penjagaHijauGallery' => PenjagaHijauKota::gallery(),
            'pimpinanKota' => PemerintahKotaBatam::pimpinan(),
            'visiMisiKota' => PemerintahKotaBatam::visiMisi(),
            'totalKuisPertanyaan' => count(EnsiklopediaInteraktif::kuis()),
        ]);
    }
}
