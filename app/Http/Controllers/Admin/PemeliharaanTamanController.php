<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlatSaranaOperasional;
use App\Support\OperasionalPelaksanaanTime;
use App\Models\PemeliharaanTaman;
use App\Models\PemeliharaanTamanArmada;
use App\Models\Taman;
use App\Support\TableSearch;
use App\Support\TimPelaksanaResolver;
use App\Support\OperatorWilayahScope;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PemeliharaanTamanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PemeliharaanTaman::class, 'pemeliharaan_taman', [
            'except' => ['show'],
        ]);
    }

    public function index(Request $request): View
    {
        $scope = app(OperatorWilayahScope::class);

        $taman = $request->filled('taman_id')
            ? Taman::findOrFail($request->integer('taman_id'))
            : null;

        if ($taman && $request->user() && ! $scope->canAccessTaman($request->user(), $taman)) {
            abort(403);
        }

        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request, $taman !== null);

        $kinerjas = $scope->scopePemeliharaan(
            PemeliharaanTaman::query()->with(['taman', 'armadas']),
            $request->user(),
        )
            ->when($taman, fn ($q) => $q->where('taman_id', $taman->id))
            ->when(! $taman || $this->hasDateRangeFilter($request), function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereDate('tanggal', '>=', $tanggalMulai)
                    ->whereDate('tanggal', '<=', $tanggalSelesai);
            })
            ->when($request->filled('tim'), fn ($q) => $q->where('tim', $request->tim))
            ->tap(fn ($q) => TableSearch::apply($q, $request, ['lokasi_pelaksanaan', 'uraian_pekerjaan', 'tim']))
            ->orderByDesc('tanggal')
            ->orderBy('tim')
            ->paginate(15)
            ->withQueryString();

        $ringkasanTim = $taman
            ? collect()
            : collect(PemeliharaanTaman::timNames())->mapWithKeys(function (string $tim) use ($tanggalMulai, $tanggalSelesai, $scope, $request) {
                $allowedTeams = $scope->teamNames($request->user());

                if ($scope->restrictsWilayah($request->user()) && ! in_array($tim, $allowedTeams, true)) {
                    return [$tim => 0];
                }

                return [$tim => $scope->scopePemeliharaan(
                    PemeliharaanTaman::query()->where('tim', $tim),
                    $request->user(),
                )
                    ->whereDate('tanggal', '>=', $tanggalMulai)
                    ->whereDate('tanggal', '<=', $tanggalSelesai)
                    ->count()];
            });

        return view('admin.pemeliharaan_tamans.index', [
            'kinerjas' => $kinerjas,
            'tanggalMulai' => $tanggalMulai->toDateString(),
            'tanggalSelesai' => $tanggalSelesai->toDateString(),
            'ringkasanTim' => $ringkasanTim,
            'taman' => $taman,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('admin.pemeliharaan_tamans.create', [
            'tamans' => $this->tamanOptions($user),
            'prefillTamanId' => $request->integer('taman_id') ?: null,
            'timWilayahKelurahan' => app(TimPelaksanaResolver::class)->kelurahanIdsByTeamName(),
            ...$this->operatorTimViewData($user),
            ...$this->armadaFormData(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateOperasional($request);
        $this->applyTamanLokasi($request, $validated);
        $this->storeUploadedFotos($request, $validated);
        $armadaRows = $this->extractArmadaRows($request, $validated['tim']);

        $pemeliharaan = PemeliharaanTaman::create($validated);
        $this->syncArmadas($pemeliharaan, $armadaRows);

        return redirect()
            ->route('admin.pemeliharaan-tamans.index', [
                'tanggal_mulai' => OperasionalPelaksanaanTime::datePart($validated['tanggal']),
                'tanggal_selesai' => OperasionalPelaksanaanTime::datePart($validated['tanggal']),
            ])
            ->with('success', 'Operasional pemeliharaan taman berhasil dicatat.');
    }

    public function edit(PemeliharaanTaman $pemeliharaanTaman): View
    {
        $user = auth()->user();

        return view('admin.pemeliharaan_tamans.edit', [
            'kinerja' => $pemeliharaanTaman->load('armadas'),
            'tamans' => $this->tamanOptions($user),
            'timWilayahKelurahan' => app(TimPelaksanaResolver::class)->kelurahanIdsByTeamName(),
            ...$this->operatorTimViewData($user),
            ...$this->armadaFormData(),
        ]);
    }

    public function update(Request $request, PemeliharaanTaman $pemeliharaanTaman)
    {
        $validated = $this->validateOperasionalForUpdate($request, $pemeliharaanTaman);
        $this->applyTamanLokasi($request, $validated);
        $this->storeUploadedFotos($request, $validated, $pemeliharaanTaman);
        $armadaRows = $this->extractArmadaRows($request, $validated['tim']);

        $pemeliharaanTaman->update($validated);
        $this->syncArmadas($pemeliharaanTaman, $armadaRows);

        return redirect()
            ->route('admin.pemeliharaan-tamans.index', [
                'tanggal_mulai' => OperasionalPelaksanaanTime::datePart($validated['tanggal']),
                'tanggal_selesai' => OperasionalPelaksanaanTime::datePart($validated['tanggal']),
            ])
            ->with('success', 'Operasional pemeliharaan taman berhasil diperbarui.');
    }

    public function destroy(PemeliharaanTaman $pemeliharaanTaman)
    {
        $this->deleteFotos($pemeliharaanTaman);
        $pemeliharaanTaman->delete();

        return redirect()
            ->route('admin.pemeliharaan-tamans.index')
            ->with('success', 'Data operasional berhasil dihapus.');
    }

    public function exportPdf(Request $request): Response
    {
        $this->ensureGdLoaded();

        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'tanggal' => ['nullable', 'date'],
            'tim' => ['nullable', Rule::in(PemeliharaanTaman::timNames())],
        ]);

        if (filled($validated['tanggal'] ?? null) && blank($validated['tanggal_mulai'] ?? null)) {
            $validated['tanggal_mulai'] = $validated['tanggal'];
            $validated['tanggal_selesai'] = $validated['tanggal'];
        }

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai'] ?? now()->toDateString())->startOfDay();
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai'] ?? $tanggalMulai->toDateString())->startOfDay();

        if ($tanggalSelesai->lt($tanggalMulai)) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        $kinerjas = app(OperatorWilayahScope::class)->scopePemeliharaan(
            PemeliharaanTaman::query()
                ->with(['armadas.alatSarana', 'taman'])
                ->whereDate('tanggal', '>=', $tanggalMulai)
                ->whereDate('tanggal', '<=', $tanggalSelesai)
                ->when($validated['tim'] ?? null, fn ($q, $tim) => $q->where('tim', $tim)),
            $request->user(),
        )
            ->orderBy('tanggal')
            ->orderBy('tim')
            ->orderBy('lokasi_pelaksanaan')
            ->get()
            ->groupBy('tim');

        $html = view('admin.pemeliharaan_tamans.pdf', [
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'kinerjasPerTim' => $kinerjas,
            'timFilter' => $validated['tim'] ?? null,
            'totalOperasional' => $kinerjas->flatten()->count(),
        ])->render();

        $filename = 'operasional-pemeliharaan-taman-'.$tanggalMulai->format('Y-m-d');
        if (! $tanggalMulai->isSameDay($tanggalSelesai)) {
            $filename .= '-'.$tanggalSelesai->format('Y-m-d');
        }
        if (! empty($validated['tim'])) {
            $filename .= '-'.str_replace(' ', '-', strtolower($validated['tim']));
        }
        $filename .= '.pdf';

        return $this->downloadPdf($html, $filename);
    }

    public function exportPdfOperasional(PemeliharaanTaman $pemeliharaanTaman): Response
    {
        $this->authorize('view', $pemeliharaanTaman);

        $this->ensureGdLoaded();

        $html = view('admin.pemeliharaan_tamans.pdf_operasional', [
            'kinerja' => $pemeliharaanTaman->load(['armadas.alatSarana', 'taman']),
        ])->render();

        $slug = str($pemeliharaanTaman->lokasi_pelaksanaan)->slug('-')->limit(30, '');
        $filename = 'operasional-'.$pemeliharaanTaman->tanggal->format('Y-m-d').'-'.$slug.'.pdf';

        return $this->downloadPdf($html, $filename);
    }

    private function ensureGdLoaded(): void
    {
        if (! extension_loaded('gd')) {
            abort(503, 'Ekstensi PHP GD belum aktif. Aktifkan extension=gd di php.ini (XAMPP: C:\\xampp\\php\\php.ini) lalu restart server PHP.');
        }
    }

    private function downloadPdf(string $html, string $filename): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function validateOperasional(Request $request): array
    {
        $validated = $this->validateOperasionalFields($request);
        app(OperatorWilayahScope::class)->enforceOperatorTim($request->user(), $validated);

        if (! $request->boolean('lokasi_luar')) {
            $this->ensureTamanInTeamWilayah($validated['tim'], (int) $validated['taman_id']);
        }

        return $validated;
    }

    private function validateOperasionalForUpdate(Request $request, PemeliharaanTaman $kinerja): array
    {
        $validated = $this->validateOperasionalFieldsForUpdate($request, $kinerja);
        app(OperatorWilayahScope::class)->enforceOperatorTim($request->user(), $validated);

        if (! $request->boolean('lokasi_luar')) {
            $this->ensureTamanInTeamWilayah($validated['tim'], (int) $validated['taman_id']);
        }

        return $validated;
    }

    private function ensureTamanInTeamWilayah(string $tim, int $tamanId): void
    {
        if (! app(TimPelaksanaResolver::class)->tamanAllowedForTeam($tim, $tamanId)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'taman_id' => 'Lokasi pelaksanaan tidak termasuk wilayah kerja '.$tim.'.',
            ]);
        }
    }

    private function validateOperasionalFields(Request $request): array
    {
        $isLokasiLuar = $request->boolean('lokasi_luar');

        $rules = [
            'tanggal' => ['required', 'date'],
            'tim' => ['required', Rule::in(PemeliharaanTaman::timNames())],
            'lokasi_luar' => ['nullable', 'boolean'],
            'jumlah_personil' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'hari_ke' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'total_hari' => ['nullable', 'integer', 'min:1', 'max:9999', 'gte:hari_ke'],
            'persentase_progres' => ['nullable', 'integer', 'min:0', 'max:100'],
            'uraian_pekerjaan' => ['nullable', 'string'],
        ];

        if ($request->input('tim') === PemeliharaanTaman::TIM_ARMADA) {
            $rules['armada'] = ['required', 'array', 'min:1'];
            $rules['armada.*.alat_sarana_operasional_id'] = [
                'required',
                'integer',
                Rule::exists('alat_sarana_operasionals', 'id')->where('peruntukan', PemeliharaanTaman::TIM_ARMADA),
            ];
            $rules['armada.*.sopir'] = ['required', Rule::in(PemeliharaanTamanArmada::SOPIR_OPTIONS)];
        } else {
            $rules['armada'] = ['nullable', 'array'];
            $rules['armada.*.alat_sarana_operasional_id'] = ['nullable', 'integer', 'exists:alat_sarana_operasionals,id'];
            $rules['armada.*.sopir'] = ['nullable', 'string', Rule::in(PemeliharaanTamanArmada::SOPIR_OPTIONS)];
        }

        if ($isLokasiLuar) {
            $rules['lokasi_pelaksanaan'] = ['required', 'string', 'max:255'];
        } else {
            $rules['taman_id'] = ['required', 'exists:tamans,id'];
        }

        $attributes = [
            'tanggal' => 'tanggal & waktu pelaksanaan',
            'tim' => 'tim',
            'taman_id' => 'lokasi pelaksanaan',
            'lokasi_pelaksanaan' => 'lokasi pelaksanaan',
            'jumlah_personil' => 'jumlah personil',
            'hari_ke' => 'hari ke',
            'total_hari' => 'total hari',
            'persentase_progres' => 'persentase progres',
            'uraian_pekerjaan' => 'uraian pekerjaan',
            'armada' => 'data armada',
            'armada.*.alat_sarana_operasional_id' => 'nama armada',
            'armada.*.sopir' => 'sopir',
        ];

        foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
            $rules[$field] = ['required', 'image', 'max:4096'];
            $attributes[$field] = strtolower($label);
        }

        $validated = $request->validate($rules, [], $attributes);

        unset($validated['armada']);

        $validated['tanggal'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal']);

        return $validated;
    }

    private function validateOperasionalFieldsForUpdate(Request $request, PemeliharaanTaman $kinerja): array
    {
        $isLokasiLuar = $request->boolean('lokasi_luar');

        $rules = [
            'tanggal' => ['required', 'date'],
            'tim' => ['required', Rule::in(PemeliharaanTaman::timNames())],
            'lokasi_luar' => ['nullable', 'boolean'],
            'jumlah_personil' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'hari_ke' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'total_hari' => ['nullable', 'integer', 'min:1', 'max:9999', 'gte:hari_ke'],
            'persentase_progres' => ['nullable', 'integer', 'min:0', 'max:100'],
            'uraian_pekerjaan' => ['nullable', 'string'],
        ];

        if ($request->input('tim') === PemeliharaanTaman::TIM_ARMADA) {
            $rules['armada'] = ['required', 'array', 'min:1'];
            $rules['armada.*.alat_sarana_operasional_id'] = [
                'required',
                'integer',
                Rule::exists('alat_sarana_operasionals', 'id')->where('peruntukan', PemeliharaanTaman::TIM_ARMADA),
            ];
            $rules['armada.*.sopir'] = ['required', Rule::in(PemeliharaanTamanArmada::SOPIR_OPTIONS)];
        } else {
            $rules['armada'] = ['nullable', 'array'];
            $rules['armada.*.alat_sarana_operasional_id'] = ['nullable', 'integer', 'exists:alat_sarana_operasionals,id'];
            $rules['armada.*.sopir'] = ['nullable', 'string', Rule::in(PemeliharaanTamanArmada::SOPIR_OPTIONS)];
        }

        if ($isLokasiLuar) {
            $rules['lokasi_pelaksanaan'] = ['required', 'string', 'max:255'];
        } else {
            $rules['taman_id'] = ['required', 'exists:tamans,id'];
        }

        $attributes = [
            'tanggal' => 'tanggal & waktu pelaksanaan',
            'tim' => 'tim',
            'taman_id' => 'lokasi pelaksanaan',
            'lokasi_pelaksanaan' => 'lokasi pelaksanaan',
            'jumlah_personil' => 'jumlah personil',
            'hari_ke' => 'hari ke',
            'total_hari' => 'total hari',
            'persentase_progres' => 'persentase progres',
            'uraian_pekerjaan' => 'uraian pekerjaan',
            'armada' => 'data armada',
            'armada.*.alat_sarana_operasional_id' => 'nama armada',
            'armada.*.sopir' => 'sopir',
        ];

        foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
            $rules[$field] = ['nullable', 'image', 'max:4096'];

            if (! $kinerja->{$field}) {
                $rules[$field] = ['required', 'image', 'max:4096'];
            }

            $attributes[$field] = strtolower($label);
        }

        $validated = $request->validate($rules, [], $attributes);

        unset($validated['armada']);

        $validated['tanggal'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal']);

        return $validated;
    }

    private function storeUploadedFotos(Request $request, array &$validated, ?PemeliharaanTaman $kinerja = null): void
    {
        foreach (PemeliharaanTaman::fotoFieldKeys() as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            if ($kinerja?->{$field}) {
                Storage::disk('public')->delete($kinerja->{$field});
            }

            $validated[$field] = $request->file($field)->store('pemeliharaan-taman', 'public');
        }
    }

    private function deleteFotos(PemeliharaanTaman $kinerja): void
    {
        foreach (PemeliharaanTaman::fotoFieldKeys() as $field) {
            if ($kinerja->{$field}) {
                Storage::disk('public')->delete($kinerja->{$field});
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Taman>
     */
    private function tamanOptions(?\App\Models\User $user = null)
    {
        $query = Taman::query()->orderBy('nama_taman');

        if ($user) {
            $query = app(OperatorWilayahScope::class)->scopeTamans($query, $user);
        }

        return $query->get(['id', 'nama_taman', 'alamat', 'kategori', 'kelurahan_id']);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function applyTamanLokasi(Request $request, array &$validated): void
    {
        if ($request->boolean('lokasi_luar')) {
            $validated['taman_id'] = null;
        } else {
            $taman = Taman::query()->findOrFail($validated['taman_id']);
            $validated['lokasi_pelaksanaan'] = PemeliharaanTaman::lokasiLabelFromTaman($taman);
        }

        unset($validated['lokasi_luar']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(Request $request, bool $optionalForTaman): array
    {
        if ($request->filled('tanggal') && ! $request->filled('tanggal_mulai') && ! $request->filled('tanggal_selesai')) {
            $date = Carbon::parse($request->input('tanggal'))->startOfDay();

            return [$date, $date];
        }

        $hasRange = $request->filled('tanggal_mulai') || $request->filled('tanggal_selesai');

        if ($optionalForTaman && ! $hasRange) {
            $today = now()->startOfDay();

            return [$today, $today];
        }

        $start = Carbon::parse($request->input('tanggal_mulai', now()->toDateString()))->startOfDay();
        $end = Carbon::parse($request->input('tanggal_selesai', $request->input('tanggal_mulai', now()->toDateString())))->startOfDay();

        if ($end->lt($start)) {
            return [$end, $start];
        }

        return [$start, $end];
    }

    private function hasDateRangeFilter(Request $request): bool
    {
        return $request->filled('tanggal_mulai')
            || $request->filled('tanggal_selesai')
            || $request->filled('tanggal');
    }

    /**
     * @return array{operatorTim: ?string, operatorTimOptions: ?list<string>}
     */
    private function operatorTimViewData(?\App\Models\User $user): array
    {
        $allowed = app(OperatorWilayahScope::class)->allowedTimNamesForForm($user);

        if (! is_array($allowed)) {
            return ['operatorTim' => null, 'operatorTimOptions' => null];
        }

        if (count($allowed) === 1) {
            return ['operatorTim' => $allowed[0], 'operatorTimOptions' => null];
        }

        return ['operatorTim' => null, 'operatorTimOptions' => $allowed];
    }

    /**
     * @return array{armadaInventory: \Illuminate\Database\Eloquent\Collection<int, AlatSaranaOperasional>}
     */
    private function armadaFormData(): array
    {
        return [
            'armadaInventory' => AlatSaranaOperasional::query()
                ->armadaInventory()
                ->orderBy('nama')
                ->get(['id', 'nama', 'jenis']),
        ];
    }

    /**
     * @return list<array{alat_sarana_operasional_id: int, jenis_armada: string, no_plat: string, sopir: string}>
     */
    private function extractArmadaRows(Request $request, string $tim): array
    {
        if ($tim !== PemeliharaanTaman::TIM_ARMADA) {
            return [];
        }

        $inputRows = collect($request->input('armada', []))
            ->filter(fn ($row) => filled($row['alat_sarana_operasional_id'] ?? null))
            ->values();

        $ids = $inputRows
            ->pluck('alat_sarana_operasional_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $inventory = AlatSaranaOperasional::query()
            ->armadaInventory()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $inputRows->map(function (array $row) use ($inventory) {
            $id = (int) $row['alat_sarana_operasional_id'];
            /** @var AlatSaranaOperasional|null $item */
            $item = $inventory->get($id);

            if (! $item) {
                return null;
            }

            return [
                'alat_sarana_operasional_id' => $item->id,
                'jenis_armada' => $item->jenis,
                'no_plat' => '',
                'sopir' => (string) ($row['sopir'] ?? ''),
            ];
        })->filter()->values()->all();
    }

    /**
     * @param  list<array{alat_sarana_operasional_id: int, jenis_armada: string, no_plat: string, sopir: string}>  $rows
     */
    private function syncArmadas(PemeliharaanTaman $pemeliharaan, array $rows): void
    {
        $pemeliharaan->armadas()->delete();

        foreach ($rows as $index => $row) {
            $pemeliharaan->armadas()->create([
                ...$row,
                'urutan' => $index,
            ]);
        }
    }
}
