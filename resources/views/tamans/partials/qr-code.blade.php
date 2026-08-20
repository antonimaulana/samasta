@props(['url', 'label' => 'Scan untuk profil taman', 'size' => 160, 'canvasId' => null])

@php
    $canvasId = $canvasId ?? 'qr-'.md5($url);
@endphp

<div class="qr-code-widget inline-flex flex-col items-center rounded-xl border border-green-100 bg-green-50/50 p-4 text-center"
     data-qr-url="{{ $url }}"
     data-qr-size="{{ $size }}"
     data-qr-canvas="{{ $canvasId }}">
    <canvas id="{{ $canvasId }}" width="{{ $size }}" height="{{ $size }}" class="rounded-lg bg-white p-2 shadow-sm"></canvas>
    <p class="mt-3 text-xs font-medium text-gray-600">{{ $label }}</p>
    <button type="button"
            class="qr-download-btn mt-2 rounded-lg border border-green-300 bg-white px-3 py-1.5 text-xs font-semibold text-green-700 transition hover:bg-green-50"
            data-qr-canvas="{{ $canvasId }}"
            data-qr-filename="qr-taman.png">
        Unduh QR Code
    </button>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/qrcode.min.js') }}"></script>
        <script>
            onPageReady(function () {
                function renderQrCodes() {
                    if (typeof QRCode === 'undefined') {
                        return false;
                    }

                    document.querySelectorAll('.qr-code-widget').forEach(function (widget) {
                        if (widget.dataset.qrRendered === '1') {
                            return;
                        }

                        const url = widget.dataset.qrUrl;
                        const size = parseInt(widget.dataset.qrSize || '160', 10);
                        const canvas = document.getElementById(widget.dataset.qrCanvas);

                        if (!canvas || !url) {
                            return;
                        }

                        QRCode.toCanvas(canvas, url, {
                            width: size,
                            margin: 1,
                            color: { dark: '#166534', light: '#ffffff' },
                        }, function (error) {
                            if (!error) {
                                widget.dataset.qrRendered = '1';
                            }
                        });
                    });

                    return true;
                }

                if (!renderQrCodes()) {
                    let attempts = 0;
                    const timer = setInterval(function () {
                        attempts += 1;
                        if (renderQrCodes() || attempts >= 20) {
                            clearInterval(timer);
                        }
                    }, 100);
                }

                document.querySelectorAll('.qr-download-btn').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const canvas = document.getElementById(button.dataset.qrCanvas);
                        if (!canvas) return;

                        const link = document.createElement('a');
                        link.download = button.dataset.qrFilename || 'qr-code.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    });
                });
            });
        </script>
    @endpush
@endonce
