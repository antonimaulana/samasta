@php
    $formatRthValue = function (array $row, int $year): string {
        $value = $row['values'][$year] ?? 0;

        return match ($row['format']) {
            'area', 'count' => number_format((float) $value, 0, ',', '.'),
            'percent' => number_format((float) $value, 2, ',', '.').'%',
            default => (string) $value,
        };
    };
@endphp

<x-admin.data-table class="mx-5 mb-5 mt-5">
    <thead class="bg-gray-50">
        <tr>
            <th class="min-w-[22rem] px-4 py-3 text-left text-sm font-medium text-gray-700">Keterangan</th>
            @foreach ($rthYearlySummary['years'] as $year)
                <th class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-700">{{ $year }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach ($rthYearlySummary['rows'] as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $row['label'] }}</td>
                @foreach ($rthYearlySummary['years'] as $year)
                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">
                        {{ $formatRthValue($row, $year) }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</x-admin.data-table>
