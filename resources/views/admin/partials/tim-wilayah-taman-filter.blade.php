@props([
    'timWilayahKelurahan' => [],
    'timSelectId' => 'tim',
    'tamanInputId' => 'taman_id',
])

@push('scripts')
    <script>
        (function () {
            const timSelectId = @json($timSelectId);
            const tamanInputId = @json($tamanInputId);
            const timWilayahKelurahan = @json($timWilayahKelurahan);

            function bootTimWilayahFilter() {
                const timSelect = document.getElementById(timSelectId);
                const tamanHidden = document.getElementById(tamanInputId);

                if (!timSelect || !tamanHidden) {
                    return;
                }

                const tamanRoot = tamanHidden.closest('[data-searchable-select]');

                if (!tamanRoot) {
                    return;
                }

                function applyWilayahFilter(preserveValue) {
                    const teamName = timSelect.value;
                    let kelurahanIds = null;

                    if (teamName) {
                        kelurahanIds = timWilayahKelurahan[teamName] ?? null;
                    }

                    tamanRoot.dispatchEvent(new CustomEvent('searchable-select:filter-kelurahan', {
                        bubbles: true,
                        detail: {
                            kelurahanIds: kelurahanIds,
                            preserveValue: preserveValue === true,
                        },
                    }));
                }

                timSelect.addEventListener('change', function () {
                    applyWilayahFilter(false);
                });

                function applyWhenReady() {
                    if (tamanRoot.dataset.searchableReady !== 'true') {
                        return false;
                    }

                    applyWilayahFilter(true);
                    return true;
                }

                if (!applyWhenReady()) {
                    tamanRoot.addEventListener('searchable-select:ready', function () {
                        applyWilayahFilter(true);
                    }, { once: true });

                    let attempts = 0;
                    const timer = setInterval(function () {
                        attempts += 1;
                        if (applyWhenReady() || attempts >= 40) {
                            clearInterval(timer);
                        }
                    }, 50);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootTimWilayahFilter);
            } else {
                bootTimWilayahFilter();
            }
        })();
    </script>
@endpush
