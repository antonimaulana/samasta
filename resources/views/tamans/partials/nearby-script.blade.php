@once
    @push('scripts')
        <script>
            window.findNearbyTamans = function (baseUrl, extraParams) {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung geolokasi.');
                    return;
                }

                const button = document.activeElement;
                if (button?.dataset) {
                    button.dataset.loading = '1';
                    button.disabled = true;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const params = new URLSearchParams(extraParams || {});
                        params.set('nearby', '1');
                        params.set('lat', position.coords.latitude);
                        params.set('lng', position.coords.longitude);
                        window.location.href = baseUrl + '?' + params.toString();
                    },
                    function () {
                        if (button?.dataset) {
                            button.dataset.loading = '0';
                            button.disabled = false;
                        }
                        alert('Tidak dapat mengakses lokasi. Izinkan akses lokasi di browser Anda.');
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
                );
            };
        </script>
    @endpush
@endonce
