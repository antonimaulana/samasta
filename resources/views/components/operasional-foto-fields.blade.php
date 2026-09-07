@props([
    'record' => null,
    'variant' => 'admin',
    'required' => true,
])

@php
    $fotoGroups = [
        'Sebelum Pelaksanaan' => ['foto_sebelum_1', 'foto_sebelum_2'],
        'Saat Pelaksanaan' => ['foto_saat_1', 'foto_saat_2'],
        'Sesudah Pelaksanaan' => ['foto_sesudah_1', 'foto_sesudah_2'],
    ];
    $isLapangan = $variant === 'lapangan';
@endphp

@foreach ($fotoGroups as $groupLabel => $fields)
    <div @class([
        'mt-8 border-t border-gray-100 pt-6' => ! $isLapangan,
        'mt-4' => $isLapangan,
    ])>
        <h3 @class([
            'mb-4 text-sm font-bold text-green-800' => ! $isLapangan,
            'lapangan-section-title mb-3 text-sm font-black text-emerald-900' => $isLapangan,
        ])>{{ $groupLabel }}</h3>
        <div class="grid gap-4 sm:grid-cols-2 md:gap-6">
            @foreach ($fields as $field)
                @php
                    $label = \App\Models\PemeliharaanTaman::FOTO_FIELDS[$field];
                    $hasExisting = filled($record?->{$field});
                    $mustUpload = $required && ! $hasExisting;
                @endphp
                <div @class([
                    'rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-4' => $isLapangan,
                ])>
                    <label for="{{ $field }}" @class([
                        'mb-1 block text-sm font-medium text-gray-700' => ! $isLapangan,
                        'block text-sm font-black text-emerald-900' => $isLapangan,
                    ])>
                        {{ $label }} @if ($mustUpload)<span class="text-red-600">*</span>@endif
                    </label>
                    <input type="file" name="{{ $field }}" id="{{ $field }}" accept="image/*"
                           @if ($mustUpload) required @endif
                           @class([
                               'w-full rounded-lg border border-gray-300 px-3 py-2 file:mr-3 file:rounded file:border-0 file:bg-green-50 file:px-3 file:py-1 file:text-green-700' => ! $isLapangan,
                               'mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-bold file:text-white' => $isLapangan,
                           ])>
                    @error($field)
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($hasExisting)
                        <img src="{{ $record->fotoUrl($field) }}" alt="{{ $label }}"
                             @class([
                                 'mt-3 h-32 w-full rounded-lg border object-cover' => ! $isLapangan,
                                 'mt-3 h-36 w-full rounded-xl border border-gray-200 object-cover shadow-sm' => $isLapangan,
                             ])>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endforeach
