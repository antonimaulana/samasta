<?php

namespace App\Http\Controllers;

use App\Support\PerdaKetertibanUmum;
use Illuminate\View\View;

class PeraturanController extends Controller
{
    public function index(): View
    {
        return view('peraturan.index', [
            'pengantar' => PerdaKetertibanUmum::pengantar(),
            'aturan' => PerdaKetertibanUmum::aturanPertamanan(),
            'sanksi' => PerdaKetertibanUmum::sanksi(),
            'sumber' => PerdaKetertibanUmum::sumberAcuan(),
            'perdaUtama' => PerdaKetertibanUmum::PERDA_UTAMA,
            'perdaPembaruan' => PerdaKetertibanUmum::PERDA_PEMBARUAN,
        ]);
    }
}
