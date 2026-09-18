@props([
    'placeholder' => 'Cari data...',
    'preserve' => null,
    'live' => true,
])

@php
    $preserveParams = $preserve ?? collect(request()->query())->except(['search', 'page'])->all();
@endphp

<form method="GET"
      @if ($live) data-live-table-search @endif
      {{ $attributes->merge(['class' => 'mb-4']) }}>
    @foreach ($preserveParams as $key => $value)
        @if (is_scalar($value) && (string) $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="min-w-[220px] max-w-md">
        <label for="table-search-input" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
        <input type="search" name="search" id="table-search-input"
               value="{{ request('search') }}"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    @unless ($live)
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                Cari
            </button>
            @if (request()->filled('search'))
                <a href="{{ url()->current() }}?{{ http_build_query(collect($preserveParams)->except(['search'])->all()) }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            @endif
        </div>
    @endunless
</form>

@if ($live)
    @once
        @push('scripts')
            <script>
                (window.onPageReady || function (fn) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', fn);
                    } else {
                        fn();
                    }
                })(function () {
                    document.querySelectorAll('form[data-live-table-search]').forEach(function (form) {
                        const input = form.querySelector('input[name="search"]');
                        if (!input) {
                            return;
                        }

                        let debounceTimer = null;
                        let lastSubmitted = input.value;

                        function submitIfChanged() {
                            if (input.value === lastSubmitted) {
                                return;
                            }
                            lastSubmitted = input.value;
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.submit();
                            }
                        }

                        input.addEventListener('input', function () {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(submitIfChanged, 350);
                        });

                        form.addEventListener('submit', function () {
                            lastSubmitted = input.value;
                        });
                    });
                });
            </script>
        @endpush
    @endonce
@endif
