<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckAduanStatusRequest;
use App\Http\Requests\StoreAduanRequest;
use App\Models\AduanMasyarakat;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Support\OperationalAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AduanMasyarakatController extends Controller
{
    public function create(Request $request): View
    {
        $tamans = Taman::query()->orderBy('nama_taman')->get(['id', 'nama_taman', 'alamat']);

        return view('aduan.create', [
            'tamans' => $tamans,
            'selectedTamanId' => $request->integer('taman') ?: null,
        ]);
    }

    public function store(StoreAduanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('aduan-masyarakat', 'public');
        }

        $taman = Taman::query()->findOrFail($validated['taman_id']);
        $validated['lokasi'] = PemeliharaanTaman::lokasiLabelFromTaman($taman);

        $validated['nomor_aduan'] = AduanMasyarakat::generateNomor();
        $validated['status'] = 'Baru';

        $aduan = AduanMasyarakat::create($validated);

        OperationalAlertService::clearCache();

        return redirect()
            ->route('aduan.success')
            ->with('aduan_nomor', $aduan->nomor_aduan)
            ->with('success', 'Aduan Anda berhasil dikirim.');
    }

    public function success(): View|RedirectResponse
    {
        $nomor = session('aduan_nomor');

        if (! $nomor) {
            return redirect()
                ->route('aduan.create')
                ->with('info', 'Silakan kirim aduan terlebih dahulu untuk melihat konfirmasi.');
        }

        $aduan = AduanMasyarakat::query()
            ->where('nomor_aduan', $nomor)
            ->firstOrFail();

        return view('aduan.success', compact('aduan'));
    }

    public function checkStatusForm(): View
    {
        return view('aduan.check-status');
    }

    public function checkStatus(CheckAduanStatusRequest $request): View
    {
        $nomorInput = $request->validated('nomor_aduan');

        $aduan = AduanMasyarakat::query()
            ->with('taman')
            ->where('nomor_aduan', $nomorInput)
            ->first();

        if (! $aduan || ! $aduan->kontakMatchesVerification($request->validated('kontak_verifikasi'))) {
            return view('aduan.check-status', [
                'notFound' => true,
                'nomorInput' => $nomorInput,
            ]);
        }

        return view('aduan.check-status', [
            'aduan' => $aduan,
            'summary' => $aduan->publicStatusSummary(),
            'nomorInput' => $nomorInput,
        ]);
    }
}
