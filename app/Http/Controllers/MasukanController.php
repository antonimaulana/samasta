<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MasukanController extends Controller
{
    public function index(): View
    {
        return view('masukan.index');
    }
}
