<?php



namespace App\Http\Controllers;



use App\Support\RthKotaBatam;

use App\Support\RthWilayahStatisticsBuilder;

use Illuminate\View\View;



class RthController extends Controller

{

    public function index(RthWilayahStatisticsBuilder $wilayahStats): View

    {

        $rthStats = RthKotaBatam::kategori();

        $totals = RthKotaBatam::total();

        $tamanStats = $wilayahStats->totals();



        return view('rth.index', [

            'rthStats' => $rthStats,

            'rthTotalLuas' => $totals['luas'],

            'rthTotalLokasi' => $totals['lokasi'],

            'tamanStats' => $tamanStats,

            'perKecamatan' => $wilayahStats->perKecamatan(),

            'perKelurahanGrouped' => $wilayahStats->perKelurahanGrouped(),

            'perKategori' => $wilayahStats->perKategori(),

        ]);

    }

}

