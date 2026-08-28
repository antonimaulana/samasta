<?php

namespace App\Http\Controllers;

use App\Models\Taman;
use App\Support\RthArProfileBuilder;
use App\Support\RthArQrGenerator;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RthArController extends Controller
{
    public function showArProfile(Taman $taman, RthArProfileBuilder $profileBuilder): View
    {
        $profile = $profileBuilder->build($taman);

        return view('rth.ar-profile', [
            'profile' => $profile,
            'taman' => $taman,
        ]);
    }

    public function qrImage(Taman $taman, RthArQrGenerator $qrGenerator): Response
    {
        $this->authorize('view', $taman);

        $url = $qrGenerator->scanUrl($taman);

        if ($qrGenerator->preferredFormat() === 'png') {
            return response($qrGenerator->png($url), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        return response($qrGenerator->svg($url), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
