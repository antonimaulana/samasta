<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Support\ArmadaAssignment;
use App\Support\JadwalLayananQuery;
use App\Support\OperatorWilayahScope;
use App\Support\PelaksanaScheduleConflictChecker;
use App\Support\PemangkasanProgresPdf;
use App\Support\PemangkasanSchedule;
use App\Support\TableSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PemangkasanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Pemangkasan::class, 'pemangkasan');
    }

    public function index(Request $request): View
    {
        $view = $request->input('view', 'semua');
        $allowedViews = collect(JadwalLayananQuery::viewOptions())->pluck('key')->all();

        if (! in_array($view, $allowedViews, true)) {
            $view = 'semua';
        }

        $pelaksana = $request->input('pelaksana');
        $jenis = $request->input('jenis');

        $query = app(OperatorWilayahScope::class)->scopePemangkasan(
            JadwalLayananQuery::forView($view, $pelaksana, $jenis),
            $request->user(),
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->input('alert') === 'stuck', fn ($q) => $q
                ->where('status', config('alerts.layanan.stuck_status'))
                ->where('updated_at', '<=', now()->subDays(config('alerts.layanan.diproses_days'))))
            ->tap(fn ($q) => TableSearch::apply($q, $request, [
                'lokasi_pohon',
                'pelaksana',
                'asal',
                'penanggungjawab',
                'kontak_permohonan',
                'status',
                'jenis_layanan',
                'kategori',
                'dampak',
            ]));

        $pemangkasans = $query->paginate(15)->withQueryString();

        return view('admin.pemangkasans.index', [
            'pemangkasans' => $pemangkasans,
            'view' => $view,
            'viewOptions' => JadwalLayananQuery::viewOptions(),
            'counts' => $this->jadwalCountsForUser($request->user(), $pelaksana, $jenis),
            'timList' => PemeliharaanTaman::timNames(),
            'jenisList' => Pemangkasan::JENIS_LAYANAN,
            'pelaksana' => $pelaksana,
            'jenis' => $jenis,
        ]);
    }

    public function checkScheduleConflict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_eksekusi' => ['required', 'date'],
            'pelaksana' => ['required', 'array', 'min:1'],
            'pelaksana.*' => ['required', 'string'],
            'exclude_id' => ['nullable', 'integer', 'exists:pemangkasans,id'],
        ]);

        $conflicts = PelaksanaScheduleConflictChecker::find(
            $validated['tanggal_eksekusi'],
            $validated['pelaksana'],
            $validated['exclude_id'] ?? null,
        );

        return response()->json([
            'conflicts' => $conflicts,
        ]);
    }

    public function create(): View
    {
        return view('admin.pemangkasans.create', [
            'tamans' => $this->tamansForSelect(),
            'armadaInventory' => ArmadaAssignment::inventory(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePemangkasan($request, requiresArmada: false);
        $armadaRows = ArmadaAssignment::extractRows($request);

        if ($request->hasFile('pendukung_pelaksanaan')) {
            $validated['pendukung_pelaksanaan'] = $request->file('pendukung_pelaksanaan')
                ->store('pemangkasan/pendukung', 'public');
        }

        $pemangkasan = Pemangkasan::create($validated);
        ArmadaAssignment::sync($pemangkasan, $armadaRows);

        return redirect()
            ->route('admin.pemangkasans.index')
            ->with('success', 'Data operasional pertamanan berhasil ditambahkan.');
    }

    public function edit(Pemangkasan $pemangkasan): View
    {
        return view('admin.pemangkasans.edit', [
            'pemangkasan' => $pemangkasan->load(['armadas', 'progres']),
            'tamans' => $this->tamansForSelect(),
            'armadaInventory' => ArmadaAssignment::inventory(),
        ]);
    }

    public function show(Request $request, Pemangkasan $pemangkasan): View
    {
        $pemangkasan->load(['taman', 'progres']);

        $backUrl = $request->filled('view') || $request->input('from') === 'jadwal'
            ? route('admin.pemangkasans.index', array_filter([
                'view' => $request->input('view', 'semua'),
                'pelaksana' => $request->input('pelaksana'),
                'jenis' => $request->input('jenis'),
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ]))
            : route('admin.pemangkasans.index');

        $backLabel = 'Kembali ke daftar operasional';

        return view('admin.pemangkasans.show', [
            'pemangkasan' => $pemangkasan,
            'backUrl' => $backUrl,
            'backLabel' => $backLabel,
        ]);
    }

    public function update(Request $request, Pemangkasan $pemangkasan): RedirectResponse
    {
        $validated = $this->validatePemangkasan($request, $pemangkasan, requiresArmada: false);
        $armadaRows = ArmadaAssignment::extractRows($request);

        if ($request->hasFile('pendukung_pelaksanaan')) {
            if ($pemangkasan->hasPendukungPelaksanaanFile()) {
                Storage::disk('public')->delete($pemangkasan->pendukung_pelaksanaan);
            }

            $validated['pendukung_pelaksanaan'] = $request->file('pendukung_pelaksanaan')
                ->store('pemangkasan/pendukung', 'public');
        }

        $pemangkasan->update($validated);
        ArmadaAssignment::sync($pemangkasan, $armadaRows);

        return redirect()
            ->route('admin.pemangkasans.index')
            ->with('success', 'Data operasional pertamanan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Pemangkasan $pemangkasan): RedirectResponse
    {
        $this->authorize('update', $pemangkasan);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Pemangkasan::STATUS)],
        ]);

        if ($validated['status'] === 'Selesai') {
            $pemangkasan->update([
                'status' => 'Selesai',
                'tanggal_penyelesaian' => $pemangkasan->tanggal_penyelesaian ?? now()->toDateString(),
            ]);
        } else {
            $pemangkasan->update([
                'status' => $validated['status'],
                'tanggal_penyelesaian' => null,
            ]);
        }

        return back()->with('success', 'Status layanan berhasil diperbarui.');
    }

    public function exportPdfProgres(Pemangkasan $pemangkasan, PemangkasanProgres $pemangkasanProgres): Response
    {
        $this->authorize('view', $pemangkasan);

        return PemangkasanProgresPdf::download($pemangkasan, $pemangkasanProgres);
    }

    public function destroy(Pemangkasan $pemangkasan): RedirectResponse
    {
        if ($pemangkasan->foto_sebelum) {
            Storage::disk('public')->delete($pemangkasan->foto_sebelum);
        }

        if ($pemangkasan->foto_sesudah) {
            Storage::disk('public')->delete($pemangkasan->foto_sesudah);
        }

        if ($pemangkasan->hasPendukungPelaksanaanFile()) {
            Storage::disk('public')->delete($pemangkasan->pendukung_pelaksanaan);
        }

        $pemangkasan->delete();

        return redirect()
            ->route('admin.pemangkasans.index')
            ->with('success', 'Data operasional pertamanan berhasil dihapus.');
    }

    private function validatePemangkasan(Request $request, ?Pemangkasan $pemangkasan = null, bool $requiresArmada = false): array
    {
        $isMiniGarden = $request->input('jenis_layanan') === 'Pemasangan Mini Garden';
        $isLokasiLuar = $request->boolean('lokasi_luar');

        $rules = [
            'jenis_layanan' => ['required', Rule::in(Pemangkasan::JENIS_LAYANAN)],
            'lokasi_luar' => ['nullable', 'boolean'],
            'asal' => ['required', 'string', 'max:255'],
            'penanggungjawab' => ['required', 'string', 'max:255'],
            'kontak_permohonan' => ['required', 'string', 'min:9', 'max:50', 'regex:/^[0-9+\s\-]+$/'],
            'tanggal_permohonan' => ['required', 'date'],
            'kategori' => ['required', Rule::in(Pemangkasan::KATEGORI)],
            'dampak' => ['nullable', 'string'],
            'pendukung_pelaksanaan' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,pdf',
                'max:5120',
            ],
            'tanggal_eksekusi' => ['required', 'date'],
            'tanggal_akhir_jadwal' => ['required', 'date', 'after_or_equal:tanggal_eksekusi'],
            'tanggal_penyelesaian' => [
                Rule::requiredIf(fn () => $request->input('status') === 'Selesai'),
                'nullable',
                'date',
            ],
            'pelaksana' => ['required', 'array', 'min:1'],
            'pelaksana.*' => ['required', 'string', Rule::in(PemeliharaanTaman::timNames())],
            'status' => ['required', Rule::in(Pemangkasan::STATUS)],
        ];

        if (! $isMiniGarden) {
            if ($isLokasiLuar) {
                $rules['lokasi_pohon'] = ['required', 'string', 'max:255'];
            } else {
                $rules['taman_id'] = ['required', 'exists:tamans,id'];
            }
        }

        $rules = array_merge($rules, ArmadaAssignment::validationRules($requiresArmada));

        $validated = $request->validate($rules, [], array_merge([
            'pelaksana' => 'pelaksana',
            'pelaksana.*' => 'tim pelaksana',
            'penanggungjawab' => 'penanggung jawab',
            'kontak_permohonan' => 'kontak permohonan',
            'pendukung_pelaksanaan' => 'pendukung pelaksanaan',
            'tanggal_eksekusi' => 'tanggal mulai pelaksanaan',
            'tanggal_akhir_jadwal' => 'tanggal selesai pelaksanaan',
            'tanggal_penyelesaian' => 'tanggal penyelesaian',
            'taman_id' => 'lokasi taman',
            'lokasi_pohon' => 'lokasi',
        ], ArmadaAssignment::validationAttributes()));

        if ($isMiniGarden) {
            $validated['taman_id'] = null;
            $validated['lokasi_pohon'] = 'Pemasangan Mini Garden';
        } elseif ($isLokasiLuar) {
            $validated['taman_id'] = null;
        } else {
            $taman = Taman::query()->findOrFail($validated['taman_id']);
            $validated['lokasi_pohon'] = PemeliharaanTaman::lokasiLabelFromTaman($taman);
        }

        unset($validated['lokasi_luar'], $validated['armada']);

        $validated = array_merge(
            $validated,
            PemangkasanSchedule::normalizeScheduleFields(
                $validated['tanggal_eksekusi'],
                $validated['tanggal_akhir_jadwal'],
            ),
        );

        if ($validated['status'] !== 'Selesai') {
            $validated['tanggal_penyelesaian'] = null;
        }

        unset($validated['pendukung_pelaksanaan']);

        $validated['kondisi_sebelum'] = '';

        return $validated;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Taman>
     */
    private function tamansForSelect()
    {
        return app(OperatorWilayahScope::class)->scopeTamans(
            Taman::query()->orderBy('nama_taman'),
            auth()->user(),
        )->get(['id', 'nama_taman', 'kategori', 'alamat']);
    }

    /**
     * @return array{semua: int, hari_ini: int, besok: int, minggu_ini: int, rencana: int, diproses: int, terlambat: int}
     */
    private function jadwalCountsForUser(?\App\Models\User $user, ?string $pelaksana, ?string $jenis): array
    {
        $scope = app(OperatorWilayahScope::class);

        return [
            'semua' => $scope->scopePemangkasan(JadwalLayananQuery::semua($pelaksana, $jenis), $user)->count(),
            'hari_ini' => $scope->scopePemangkasan(JadwalLayananQuery::hariIni($pelaksana, $jenis), $user)->count(),
            'besok' => $scope->scopePemangkasan(JadwalLayananQuery::besok($pelaksana, $jenis), $user)->count(),
            'minggu_ini' => $scope->scopePemangkasan(JadwalLayananQuery::mingguIni($pelaksana, $jenis), $user)->count(),
            'rencana' => $scope->scopePemangkasan(JadwalLayananQuery::antrianRencana($pelaksana, $jenis), $user)->count(),
            'diproses' => $scope->scopePemangkasan(JadwalLayananQuery::diproses($pelaksana, $jenis), $user)->count(),
            'terlambat' => $scope->scopePemangkasan(JadwalLayananQuery::terlambat($pelaksana, $jenis), $user)->count(),
        ];
    }
}
