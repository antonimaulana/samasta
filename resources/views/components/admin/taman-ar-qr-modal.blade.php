@props(['taman', 'scanUrl' => null, 'qrImageUrl' => null])

@php
    $modalId = 'taman-ar-qr-modal-'.$taman->id;
    $scanUrl = $scanUrl ?? route('rth.ar-scan', $taman);
    $qrImageUrl = $qrImageUrl ?? route('admin.tamans.ar-qr', $taman);
@endphp

<div class="inline-block">
    <button type="button"
            data-ar-qr-open="{{ $modalId }}"
            class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100">
        QR WebAR
    </button>

    <div id="{{ $modalId }}"
         class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50 p-4 sm:items-center"
         role="dialog"
         aria-modal="true"
         aria-labelledby="{{ $modalId }}-title">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h3 id="{{ $modalId }}-title" class="text-base font-bold text-gray-900">QR Code WebAR</h3>
                    <p class="mt-0.5 text-sm text-gray-500">Tempel di lokasi RTH untuk scan profil AR.</p>
                </div>
                <button type="button" data-ar-qr-close class="rounded-lg p-2 text-gray-400 hover:bg-gray-100" aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div id="taman-ar-qr-print-area-{{ $taman->id }}" class="space-y-4 px-5 py-5 text-center">
                <img src="{{ $qrImageUrl }}"
                     width="240"
                     height="240"
                     alt="QR Code WebAR {{ $taman->nama_taman }}"
                     class="mx-auto rounded-xl border border-emerald-100 bg-white p-3 shadow-sm">
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $taman->nama_taman }}</p>
                    <p class="mt-1 text-xs text-gray-500">Scan untuk profil WebAR {{ config('app.name') }}</p>
                    <p class="mt-2 break-all text-[10px] text-gray-400">{{ $scanUrl }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-gray-200 bg-gray-50 px-5 py-4">
                <button type="button"
                        data-ar-qr-print="{{ $modalId }}"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Cetak / Print Preview
                </button>
                <a href="{{ $qrImageUrl }}"
                   download="qr-webar-{{ $taman->id }}-{{ \Illuminate\Support\Str::slug($taman->nama_taman) }}.{{ extension_loaded('imagick') ? 'png' : 'svg' }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Unduh PNG
                </a>
                <a href="{{ $scanUrl }}" target="_blank" rel="noopener"
                   class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100">
                    Buka Halaman AR
                </a>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            @media print {
                body * { visibility: hidden !important; }
                [id^="taman-ar-qr-print-area-"], [id^="taman-ar-qr-print-area-"] * { visibility: visible !important; }
                [id^="taman-ar-qr-print-area-"] {
                    position: fixed;
                    inset: 10% 15%;
                    width: 70%;
                    border: 2px solid #059669;
                    border-radius: 12px;
                    padding: 24px;
                }
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            onPageReady(function () {
                function openModal(id) {
                    const modal = document.getElementById(id);
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal(modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                }

                document.querySelectorAll('[data-ar-qr-open]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        openModal(button.dataset.arQrOpen);
                    });
                });

                document.querySelectorAll('[id^="taman-ar-qr-modal-"]').forEach(function (modal) {
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) {
                            closeModal(modal);
                        }
                    });

                    modal.querySelectorAll('[data-ar-qr-close]').forEach(function (button) {
                        button.addEventListener('click', function () {
                            closeModal(modal);
                        });
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') return;
                    document.querySelectorAll('[id^="taman-ar-qr-modal-"]:not(.hidden)').forEach(closeModal);
                });

                document.querySelectorAll('[data-ar-qr-print]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        window.print();
                    });
                });
            });
        </script>
    @endpush
@endonce
