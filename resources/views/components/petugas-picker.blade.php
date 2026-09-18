@props([
    'teamName' => null,
    'teamNames' => [],
    'selectedIds' => [],
    'rostersByTeam' => [],
    'rosterUrl' => null,
    'timSelectId' => null,
    'variant' => 'admin',
    'required' => true,
])

@php
    $teams = collect($teamNames)->filter()->values();
    if ($teams->isEmpty() && filled($teamName)) {
        $teams = collect([$teamName]);
    }

    $initialRoster = $teams->isEmpty()
        ? []
        : collect($rostersByTeam)
            ->only($teams->all())
            ->flatten(1)
            ->values()
            ->all();

    if ($initialRoster === [] && $teams->isNotEmpty()) {
        $initialRoster = app(\App\Support\PetugasRosterBuilder::class)->forTeamNames($teams->all());
    }

    $selected = collect(old('petugas_ids', $selectedIds))
        ->map(fn ($id) => (int) $id)
        ->filter(fn (int $id) => $id > 0)
        ->values()
        ->all();

    $rosterById = collect($initialRoster)->keyBy('id');
    $initialNames = collect($selected)
        ->map(fn (int $id) => $rosterById->get($id)['nama'] ?? null)
        ->filter()
        ->values()
        ->all();
    $initialDisplay = $initialNames !== [] ? implode(';', $initialNames).';' : '';

    $isLapangan = $variant === 'lapangan';
    $hasRoster = count($initialRoster) > 0;
    $isDynamic = filled($timSelectId);
    $showPicker = $hasRoster || $isDynamic;
    $awaitingTeam = $isDynamic && $teams->isEmpty();
    $pickerId = 'petugas-picker-'.uniqid();
    $inputClass = $isLapangan
        ? 'w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-base'
        : 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm';
    $labelClass = $isLapangan ? 'text-sm font-bold text-gray-700' : 'text-sm font-medium text-gray-700';
@endphp

<div id="{{ $pickerId }}"
     class="petugas-picker {{ $isLapangan ? 'petugas-picker--lapangan' : 'petugas-picker--admin' }}"
     data-roster-url="{{ $rosterUrl ?? route('admin.tim-pelaksanas.roster') }}"
     data-tim-select-id="{{ $timSelectId }}"
     data-fixed-teams="{{ $teams->isNotEmpty() && blank($timSelectId) ? $teams->toJson() : '[]' }}"
     data-initial-roster="{{ json_encode($initialRoster) }}"
     data-initial-selected="{{ json_encode($selected) }}"
     data-required="{{ $required ? '1' : '0' }}"
     data-awaiting-team="{{ $awaitingTeam ? '1' : '0' }}">

    @if ($showPicker)
        <div data-petugas-active-wrap class="{{ ($hasRoster || $awaitingTeam) ? '' : 'hidden' }}">
            <div class="relative">
                <label class="mb-1 block {{ $labelClass }}">
                    Petugas pelaksana
                </label>
                <input type="text"
                       data-petugas-field
                       autocomplete="off"
                       value="{{ $initialDisplay }}"
                       @disabled($awaitingTeam)
                       placeholder="{{ $awaitingTeam ? 'Pilih tim pelaksana terlebih dahulu' : 'Ketik sebagian nama, pilih dari daftar. Beberapa nama dipisah ;' }}"
                       class="{{ $inputClass }} focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <ul data-petugas-dropdown
                    class="absolute z-20 mt-1 hidden max-h-56 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                </ul>
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-3">
                <button type="button" data-petugas-clear
                        class="{{ $isLapangan ? 'text-sm font-bold text-gray-600 hover:text-red-700' : 'text-sm font-medium text-gray-600 hover:text-red-700' }}">
                    Hapus semua
                </button>
                <p class="text-xs text-gray-500">
                    <span data-petugas-count>{{ count($selected) }}</span> petugas · jumlah personil mengikuti pilihan
                </p>
            </div>

            <div data-petugas-inputs></div>
            <input type="hidden" name="jumlah_personil" data-petugas-personil value="{{ count($selected) ?: old('jumlah_personil') }}">
        </div>

        <div data-petugas-manual-fallback class="{{ ($hasRoster || $awaitingTeam) ? 'hidden' : '' }} mt-3">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Daftar petugas tim belum diatur.
                @if (auth()->user()?->canManageUsers())
                    <a href="{{ route('admin.tim-pelaksanas.index') }}" class="font-semibold underline">Kelola anggota tim</a>
                @endif
            </div>
            <div class="mt-3">
                <label for="jumlah_personil_manual_{{ $pickerId }}" class="mb-1 block {{ $labelClass }}">
                    Jumlah personil {{ $required ? '*' : '' }}
                </label>
                <input type="number" name="jumlah_personil" id="jumlah_personil_manual_{{ $pickerId }}" min="1" max="9999"
                       value="{{ old('jumlah_personil') }}"
                       @if ($required) required @endif
                       class="{{ $inputClass }} focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                @error('jumlah_personil')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @error('petugas_ids')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('petugas_ids.*')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @else
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Daftar petugas tim belum diatur.
            @if (auth()->user()?->canManageUsers())
                <a href="{{ route('admin.tim-pelaksanas.index') }}" class="font-semibold underline">Kelola anggota tim</a>
            @endif
        </div>
        <div class="mt-3">
            <label for="jumlah_personil" class="mb-1 block {{ $labelClass }}">
                Jumlah personil {{ $required ? '*' : '' }}
            </label>
            <input type="number" name="jumlah_personil" id="jumlah_personil" min="1" max="9999"
                   value="{{ old('jumlah_personil') }}"
                   @if ($required) required @endif
                   class="{{ $inputClass }} focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @error('jumlah_personil')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>

@if ($showPicker)
    @push('scripts')
        <script src="{{ asset('js/petugas-picker.js') }}"></script>
    @endpush
@endif
