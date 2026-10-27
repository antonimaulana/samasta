@php
    use App\Support\PublicRthStatisticsBuilder;

    $formatValue = function (array $row, int $year): string {
        $value = $row['values'][$year] ?? null;

        return match ($row['format']) {
            'area' => PublicRthStatisticsBuilder::formatArea($value !== null ? (int) $value : null),
            'count' => PublicRthStatisticsBuilder::formatCount($value !== null ? (int) $value : null),
            'percent' => PublicRthStatisticsBuilder::formatPercent($value !== null ? (float) $value : null, 2),
            default => (string) ($value ?? '—'),
        };
    };

    $highlightKeys = ['A', 'B', 'D', 'E', 'G'];
@endphp

<div class="overflow-x-auto rounded-2xl border border-emerald-100">
    <table class="min-w-full text-sm">
        <thead class="bg-emerald-50/80">
            <tr>
                <th class="min-w-[16rem] px-4 py-3 text-left font-semibold text-emerald-900">Indikator</th>
                @foreach ($rthYearlySummary['years'] as $year)
                    <th class="whitespace-nowrap px-4 py-3 text-right font-semibold text-emerald-900">
                        {{ $year }}
                        @if ($year === $latestYear)
                            <span class="ml-1 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white">Terbaru</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-emerald-50 bg-white">
            @foreach ($rthYearlySummary['rows'] as $row)
                @php
                    $isHighlight = in_array($row['key'], $highlightKeys, true);
                @endphp
                <tr @class(['hover:bg-emerald-50/40', 'bg-emerald-50/20' => $isHighlight])>
                    <td class="px-4 py-3 text-gray-800">
                        <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-100 text-xs font-bold text-emerald-800">
                            {{ $row['key'] }}
                        </span>
                        {{ $row['label'] }}
                    </td>
                    @foreach ($rthYearlySummary['years'] as $year)
                        <td @class([
                            'whitespace-nowrap px-4 py-3 text-right',
                            'font-bold text-emerald-900' => $isHighlight && $year === $latestYear,
                            'text-gray-700' => ! ($isHighlight && $year === $latestYear),
                        ])>
                            {{ $formatValue($row, $year) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p class="mt-4 text-xs leading-relaxed text-gray-500">
    <strong>Catatan metodologi:</strong> Baris A–B dihitung dari taman terdaftar yang pemutakhirannya ≤ tahun kolom (atau belum diverifikasi).
    Baris C mengacu luasan RTRW ({{ PublicRthStatisticsBuilder::formatArea($rtrwLuasan) }} m²).
    Luas terpelihara (E) = luas dikelola (A) dikurangi penyesuaian tidak terpelihara sesuai kebijakan internal Dinas.
</p>
