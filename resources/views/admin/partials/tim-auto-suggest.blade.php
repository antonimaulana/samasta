@props([
    'tamanInputId' => 'taman_id',
    'mode' => 'checkboxes',
    'pelaksanaSelectId' => 'tim',
])

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tamanHidden = document.getElementById(@json($tamanInputId));
            if (! tamanHidden) {
                return;
            }

            const tamanRoot = tamanHidden.closest('[data-searchable-select]');
            const notice = document.getElementById('tim-suggest-notice');
            const suggestUrl = @json(route('admin.tim-pelaksanas.suggest'));
            const mode = @json($mode);

            async function applySuggestion(tamanId) {
                if (! tamanId) {
                    if (notice) {
                        notice.classList.add('hidden');
                    }

                    return;
                }

                try {
                    const response = await fetch(`${suggestUrl}?taman_id=${encodeURIComponent(tamanId)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (! response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const teams = Array.isArray(data.teams) ? data.teams : [];

                    if (teams.length === 0) {
                        if (notice) {
                            notice.textContent = 'Belum ada tim pelaksana yang ditugaskan untuk wilayah taman ini. Pilih tim secara manual atau atur mapping di menu Tim Pelaksana.';
                            notice.classList.remove('hidden');
                        }

                        return;
                    }

                    if (mode === 'select') {
                        const select = document.getElementById(@json($pelaksanaSelectId));
                        if (select) {
                            select.value = teams[0];
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    } else {
                        document.querySelectorAll('input[name="pelaksana[]"]').forEach(function (input) {
                            input.checked = teams.includes(input.value);
                            input.closest('label')?.classList.toggle('border-green-500', input.checked);
                            input.closest('label')?.classList.toggle('bg-green-50', input.checked);
                        });
                    }

                    if (notice) {
                        notice.classList.add('hidden');
                    }
                } catch (error) {
                    console.error(error);
                }
            }

            if (tamanRoot) {
                tamanRoot.addEventListener('searchable-select:change', function (event) {
                    applySuggestion(event.detail?.value || tamanHidden.value);
                });
            }

            tamanHidden.addEventListener('change', function () {
                applySuggestion(tamanHidden.value);
            });

            if (tamanHidden.value) {
                applySuggestion(tamanHidden.value);
            }
        });
    </script>
@endpush
