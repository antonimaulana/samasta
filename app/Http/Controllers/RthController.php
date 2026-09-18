<?php

namespace App\Http\Controllers;

use App\Support\PublicRthStatisticsBuilder;
use Illuminate\View\View;

class RthController extends Controller
{
    public function index(PublicRthStatisticsBuilder $statistics): View
    {
        return view('rth.index', $statistics->build());
    }
}
