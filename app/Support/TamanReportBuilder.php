<?php

namespace App\Support;

use App\Models\Taman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TamanReportBuilder
{
    /**
     * @return array{
     *     tamans: Collection<int, Taman>,
     *     tamansPerKategori: Collection<string, Collection<int, Taman>>,
     *     tamansPerWilayah: Collection<string, int>,
     *     rekapKecamatan: Collection<int, array{kecamatan: string, jumlah: int, luasan: int}>,
     *     rekapKelurahanPerKecamatan: Collection<int, array{kecamatan: string, jumlah: int, luasan: int, kelurahan: Collection<int, array{kelurahan: string, jumlah: int, luasan: int}>}>,
     *     rekapStatusData: Collection<string, int>,
     *     rekapKategori: Collection<int, array{kategori: string, jumlah: int, luasan: int}>,
     *     totalTaman: int,
     *     totalLuasan: int,
     *     search: ?string,
     *     kategori: ?string,
     *     statusData: ?string,
     *     sortState: array{sort: string, direction: string},
     *     rthYearlySummary: array<string, mixed>,
     * }
     */
    public function build(Request $request): array
    {
        $scope = app(OperatorWilayahScope::class);
        $search = trim((string) $request->input('search', ''));
        $kategori = $request->input('kategori');
        $statusData = $request->input('status_data');

        $query = TableSearch::apply(
            $scope->scopeTamans(Taman::with(['kelurahan.kecamatan']), $request->user()),
            $request,
            ['nama_taman', 'alamat', 'kategori', 'deskripsi', 'kontraktor', 'konsultan_perencana']
        )
            ->when($request->input('alert') === 'incomplete', fn (Builder $q) => app(OperationalAlertService::class)->filterIncompleteTamans($q))
            ->when(
                filled($kategori) && in_array($kategori, Taman::KATEGORI, true),
                fn (Builder $q) => $q->where('kategori', $kategori)
            )
            ->when(
                in_array($statusData, [Taman::STATUS_DATA_LENGKAP, Taman::STATUS_DATA_BELUM_LENGKAP], true),
                fn (Builder $q) => $q->where('status_data', $statusData)
            );

        $sortState = TamanTableSort::apply($query, $request);

        $tamans = $query->get();

        $tamansPerKategori = Taman::groupByKategori($tamans);

        $rekapKelurahanPerKecamatan = $tamans
            ->groupBy(fn (Taman $taman) => $taman->kelurahan?->kecamatan?->nama ?? 'Belum diset')
            ->map(function (Collection $items, string $kecamatan) {
                return [
                    'kecamatan' => $kecamatan,
                    'jumlah' => $items->count(),
                    'luasan' => (int) $items->sum('luasan'),
                    'kelurahan' => $items
                        ->groupBy(fn (Taman $taman) => $taman->kelurahan?->nama ?? 'Belum diset')
                        ->map(fn (Collection $kelurahanItems, string $kelurahan) => [
                            'kelurahan' => $kelurahan,
                            'jumlah' => $kelurahanItems->count(),
                            'luasan' => (int) $kelurahanItems->sum('luasan'),
                        ])
                        ->sortBy('kelurahan')
                        ->values(),
                ];
            })
            ->sortBy(fn (array $row) => $row['kecamatan'] === 'Belum diset' ? 'zzz' : $row['kecamatan'])
            ->values();

        $rekapKecamatan = $rekapKelurahanPerKecamatan
            ->map(fn (array $row) => [
                'kecamatan' => $row['kecamatan'],
                'jumlah' => $row['jumlah'],
                'luasan' => $row['luasan'],
            ])
            ->values();

        $tamansPerWilayah = $rekapKecamatan
            ->mapWithKeys(fn (array $row) => [$row['kecamatan'] => $row['jumlah']]);

        $rekapStatusData = collect([
            'Lengkap' => $tamans->where('status_data', Taman::STATUS_DATA_LENGKAP)->count(),
            'Belum Lengkap' => $tamans->where('status_data', Taman::STATUS_DATA_BELUM_LENGKAP)->count(),
        ]);

        $rekapKategori = collect(Taman::KATEGORI)
            ->map(function (string $label) use ($tamansPerKategori) {
                $items = $tamansPerKategori->get($label, collect());

                return [
                    'kategori' => $label,
                    'jumlah' => $items->count(),
                    'luasan' => (int) $items->sum('luasan'),
                ];
            })
            ->values();

        return [
            'tamans' => $tamans,
            'tamansPerKategori' => $tamansPerKategori,
            'tamansPerWilayah' => $tamansPerWilayah,
            'rekapKecamatan' => $rekapKecamatan,
            'rekapKelurahanPerKecamatan' => $rekapKelurahanPerKecamatan,
            'rekapStatusData' => $rekapStatusData,
            'rekapKategori' => $rekapKategori,
            'totalTaman' => $tamans->count(),
            'totalLuasan' => (int) $tamans->sum('luasan'),
            'search' => $search !== '' ? $search : null,
            'kategori' => filled($kategori) ? (string) $kategori : null,
            'statusData' => in_array($statusData, [Taman::STATUS_DATA_LENGKAP, Taman::STATUS_DATA_BELUM_LENGKAP], true)
                ? (string) $statusData
                : null,
            'sortState' => $sortState,
            'rthYearlySummary' => app(TamanRthYearlySummary::class)->build($request->user()),
        ];
    }
}
