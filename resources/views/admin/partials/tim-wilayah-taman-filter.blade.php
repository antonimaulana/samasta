@props([
    'timWilayahKelurahan' => [],
    'timSelectId' => 'tim',
    'tamanInputId' => 'taman_id',
])

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timSelect = document.getElementById(@json($timSelectId));
            const tamanHidden = document.getElementById(@json($tamanInputId));

            if (! timSelect || ! tamanHidden) {
                return;
            }

            const tamanRoot = tamanHidden.closest('[data-searchable-select]');
            const timWilayahKelurahan = @json($timWilayahKelurahan);

            function applyWilayahFilter(preserveValue) {
                if (! tamanRoot) {
                    return;
                }

                const teamName = timSelect.value;
                let kelurahanIds = null;

                if (! teamName) {
                    kelurahanIds = [];
                } else {
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

            applyWilayahFilter(true);
        });
    </script>
@endpush
