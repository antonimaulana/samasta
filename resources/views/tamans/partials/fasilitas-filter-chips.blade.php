@props([
    'popularFasilitas',
    'selectedFasilitas',
])

<div>
    <p class="mb-2 text-sm font-medium text-gray-700 sm:mb-3">Filter Fasilitas</p>
    <div class="grid grid-cols-2 gap-1.5 sm:flex sm:flex-wrap sm:gap-2">
        @foreach ($popularFasilitas as $fasilitas)
            @php $isChecked = in_array($fasilitas, $selectedFasilitas, true); @endphp
            <label class="cursor-pointer sm:max-w-none">
                <input type="checkbox"
                       name="fasilitas[]"
                       value="{{ $fasilitas }}"
                       class="peer sr-only fasilitas-filter-checkbox"
                       @checked($isChecked)>
                <span class="inline-flex w-full items-center justify-center rounded-full border px-2 py-1 text-center text-xs font-medium leading-snug transition
                             sm:w-auto sm:px-3.5 sm:py-1.5 sm:text-sm
                             {{ $isChecked
                                 ? 'border-green-600 bg-green-600 text-white shadow-sm'
                                 : 'border-green-200 bg-green-50 text-green-800 hover:border-green-400 hover:bg-green-100' }}
                             peer-focus-visible:ring-2 peer-focus-visible:ring-green-500 peer-focus-visible:ring-offset-2">
                    {{ $fasilitas }}
                </span>
            </label>
        @endforeach
    </div>
</div>
