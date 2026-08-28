@props([
    'tamans',
    'selected' => '',
    'name' => 'taman_id',
    'id' => 'taman_id',
    'required' => false,
    'placeholder' => 'Ketik nama taman, kategori, atau alamat...',
    'hint' => 'Ketik untuk memfilter, lalu pilih taman dari daftar.',
    'inputClass' => 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500',
    'createUrl' => null,
    'emptyMessage' => null,
])

@php
    use App\Support\TamanSelect;

    $tamans = collect($tamans ?? []);
    $selected = (string) old($name, $selected);
    $options = TamanSelect::options($tamans);
    $selectedLabel = TamanSelect::selectedLabel($options, $selected);
    $createUrl = $createUrl ?? (Route::has('admin.tamans.create') ? route('admin.tamans.create') : null);
    $emptyMessage = $emptyMessage ?? 'Belum ada data taman.';
@endphp

@if ($tamans->isEmpty())
    <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        {{ $emptyMessage }}
        @if ($createUrl)
            <a href="{{ $createUrl }}" class="font-semibold underline">Tambah taman</a>
            terlebih dahulu.
        @endif
    </p>
@else
    <x-admin.searchable-select
        :name="$name"
        :id="$id"
        :options="$options"
        :selected="$selected"
        :selected-label="$selectedLabel"
        :placeholder="$placeholder"
        :required="$required"
        :input-class="$inputClass"
        empty-text="Tidak ada taman yang cocok. Pilih tim pelaksana atau ketik kata kunci lain."
    />

    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }} Ketuk kolom pencarian untuk membuka daftar.</p>
    @endif
@endif
