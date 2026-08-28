@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #taman-location-map {
            height: 350px !important;
            min-height: 350px;
            width: 100%;
            background: #e5e7eb;
            z-index: 0;
        }
        #taman-location-map.leaflet-container,
        #taman-location-map .leaflet-container {
            height: 100% !important;
            width: 100%;
            font: inherit;
        }
        .leaflet-container img,
        .leaflet-container svg,
        .leaflet-container canvas {
            max-width: none !important;
            max-height: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        onPageReady(function () {
            const mapElement = document.getElementById('taman-location-map');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            if (!mapElement || !latInput || !lngInput) {
                return;
            }

            if (typeof L === 'undefined') {
                mapElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;padding:16px;text-align:center;color:#6b7280;font-size:14px;">Peta tidak dapat dimuat. Periksa koneksi internet lalu muat ulang halaman.</div>';
                return;
            }

            const defaultLat = @json(\App\Models\Taman::DEFAULT_LATITUDE);
            const defaultLng = @json(\App\Models\Taman::DEFAULT_LONGITUDE);
            const defaultZoom = @json(\App\Models\Taman::DEFAULT_MAP_ZOOM);
            const kelurahanHidden = document.getElementById('kelurahan_id');
            const kelurahanRoot = kelurahanHidden?.closest('[data-searchable-select]');
            const wilayahNotice = document.getElementById('wilayah-resolve-notice');
            const resolveUrl = @json(route('admin.tamans.resolve-wilayah'));
            let resolveTimer = null;

            function parseCoordinate(value, fallback) {
                const parsed = parseFloat(String(value ?? '').replace(',', '.').trim());

                return Number.isFinite(parsed) ? parsed : fallback;
            }

            function normalizeBatamCoordinates(lat, lng) {
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return { lat: defaultLat, lng: defaultLng };
                }

                // Batam berada di utara khatulistiwa (~1°N, ~104°E).
                if (lat < 0 && lng >= 100 && lng <= 110) {
                    lat = Math.abs(lat);
                }

                return { lat, lng };
            }

            const parsedLat = parseCoordinate(latInput.value, defaultLat);
            const parsedLng = parseCoordinate(lngInput.value, defaultLng);
            const normalized = normalizeBatamCoordinates(parsedLat, parsedLng);
            const initialLat = normalized.lat;
            const initialLng = normalized.lng;
            const hasStoredCoordinates = latInput.value.trim() !== '' && lngInput.value.trim() !== '';

            const map = L.map(mapElement, {
                scrollWheelZoom: true,
            }).setView([initialLat, initialLng], defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            }).addTo(map);

            let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            function refreshMapSize() {
                map.invalidateSize(true);
            }

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                scheduleWilayahResolve(lat, lng);
            }

            function showWilayahNotice(message, success) {
                if (!wilayahNotice) return;
                wilayahNotice.textContent = message;
                wilayahNotice.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-900', 'border-amber-200', 'bg-amber-50', 'text-amber-900');
                wilayahNotice.classList.add(success ? 'border-green-200' : 'border-amber-200', success ? 'bg-green-50' : 'bg-amber-50', success ? 'text-green-900' : 'text-amber-900');
            }

            function scheduleWilayahResolve(lat, lng) {
                clearTimeout(resolveTimer);
                resolveTimer = setTimeout(function () { resolveWilayah(lat, lng); }, 400);
            }

            async function resolveWilayah(lat, lng) {
                if (!kelurahanRoot) return;
                try {
                    const response = await fetch(`${resolveUrl}?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) return;
                    const data = await response.json();
                    if (data.resolved && data.kelurahan_id) {
                        kelurahanRoot.dispatchEvent(new CustomEvent('searchable-select:set-value', {
                            bubbles: true,
                            detail: { value: String(data.kelurahan_id), label: data.label || '' },
                        }));
                        showWilayahNotice(data.label ? `Wilayah terdeteksi: ${data.label}` : data.message, true);
                    } else {
                        showWilayahNotice(data.message || 'Wilayah tidak terdeteksi dari koordinat ini.', false);
                    }
                } catch (error) {
                    console.error(error);
                }
            }

            function moveMarker(lat, lng) {
                marker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
                updateInputs(lat, lng);
            }

            marker.on('dragend', function (event) {
                const position = event.target.getLatLng();
                updateInputs(position.lat, position.lng);
            });

            map.on('click', function (event) {
                moveMarker(event.latlng.lat, event.latlng.lng);
            });

            if (!hasStoredCoordinates || initialLat !== parsedLat || initialLng !== parsedLng) {
                updateInputs(initialLat, initialLng);
            } else {
                scheduleWilayahResolve(initialLat, initialLng);
            }

            setTimeout(refreshMapSize, 100);
            setTimeout(refreshMapSize, 400);
            window.addEventListener('load', refreshMapSize);
            window.addEventListener('resize', refreshMapSize);

            const fotosInput = document.getElementById('fotos');
            const previewContainer = document.getElementById('foto-preview');

            if (fotosInput && previewContainer) {
                fotosInput.addEventListener('change', function () {
                    previewContainer.innerHTML = '';
                    Array.from(this.files).forEach(function (file) {
                        if (!file.type.startsWith('image/')) return;
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'overflow-hidden rounded-lg border border-gray-200 bg-gray-50';
                            wrapper.innerHTML = `<div class="overflow-hidden"><img src="${event.target.result}" alt="${file.name}" class="aspect-[16/9] w-full object-cover object-center"></div><p class="truncate border-t border-gray-100 px-2 py-1.5 text-[11px] text-gray-500">${file.name}</p>`;
                            previewContainer.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }
        });
    </script>
@endpush
