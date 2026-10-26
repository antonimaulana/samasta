<?php

namespace App\Http\Controllers;

use App\Models\BerandaSlide;
use App\Support\BerandaSlidePresenter;
use App\Models\Faq;
use App\Models\FaqSetting;
use App\Support\FaqDefaults;
use App\Support\HomePageData;
use Illuminate\Support\Facades\Schema;
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
        $ensiklopediaKategoris = HomePageData::ensiklopediaKategoris();
        $presenter = app(BerandaSlidePresenter::class);
        $berandaSlides = BerandaSlide::activeOrdered()->map(
            fn (BerandaSlide $slide) => $presenter->present($slide, $stats, $ensiklopediaKategoris)
        );

        return view('home', [
            ...$stats,
            'berandaSlides' => $berandaSlides,
            'featuredTamans' => $featuredTamans,
            'heroFeaturedTamans' => TamanUnggulanHero::resolve(),
            'spotlightTaman' => $featuredTamans->first(),
            'rthTotalLuas' => $rthStats['totalLuasan'],
            'rthTotalLokasi' => $rthStats['totalTaman'],
            'rthKategoriCount' => count($rthStats['kategoriCards'] ?? []),
            'ensiklopediaKategoris' => $ensiklopediaKategoris,
            'faqSetting' => $this->faqSettingForHome(),
            'faqs' => $this->faqsForHome(),
            'penjagaHijauGallery' => PenjagaHijauKota::gallery(),
            'pimpinanKota' => PemerintahKotaBatam::pimpinan(),
            'visiMisiKota' => PemerintahKotaBatam::visiMisi(),
        ]);
    }

    private function faqSettingForHome(): FaqSetting
    {
        if (! Schema::hasTable('faq_settings')) {
            return new FaqSetting(FaqDefaults::settings());
        }

        return FaqSetting::current();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Faq>|\Illuminate\Support\Collection<int, never>
     */
    private function faqsForHome()
    {
        if (! Schema::hasTable('faqs')) {
            return collect();
        }

        return Faq::activeOrdered();
    }
}
