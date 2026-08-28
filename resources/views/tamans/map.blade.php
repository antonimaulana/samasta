@extends('layouts.public')

@section('body_class', 'bg-[#f0fdf4]')
@section('title', 'Peta Taman')

@section('meta_description', 'Peta interaktif taman hijau Kota Batam — filter kategori, lihat foto & fasilitas, dan arahkan rute menuju taman.')

@section('content')
    <div class="mb-6">
        <a href="{{ route('home') }}"
           class="mb-4 inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-semibold text-green-700 transition hover:bg-lime-100">
            ← Kembali ke beranda
        </a>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-lime-100 px-3 py-1 text-xs font-bold uppercase tracking-widest text-green-600">
                    🗺️ Peta Interaktif
                </span>
                <h1 class="mt-2 text-3xl font-black text-green-900 sm:text-4xl">Jelajahi Taman di Batam</h1>
                <p class="mt-2 text-gray-600">Pin berwarna per kategori — klik untuk foto, fasilitas, dan rute</p>
            </div>
            <p id="map-visible-count" class="rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-bold text-emerald-800">
                {{ $tamans->count() }} taman di peta
            </p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
        <aside class="h-fit overflow-hidden rounded-3xl border border-green-100 bg-white shadow-lg shadow-green-100/50 lg:sticky lg:top-24">
            <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-4">
                <h2 class="font-bold text-green-800">Filter Peta</h2>
                <p class="mt-1 text-xs text-green-700/70">Filter taman yang ditampilkan di peta</p>
            </div>
            <form action="{{ route('tamans.map') }}" method="GET" class="space-y-4 p-5">
                <div>
                    <label for="search" class="mb-1 block text-xs font-semibold text-gray-600">Nama Taman</label>
                    <input type="search" name="search" id="search" value="{{ $search }}"
                           placeholder="Cari nama..."
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
                </div>
                <div>
                    <label for="kategori" class="mb-1 block text-xs font-semibold text-gray-600">Kategori</label>
                    <select name="kategori" id="kategori"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200">
                        <option value="">Semua kategori</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori }}" @selected($selectedKategori === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold text-gray-600">Fasilitas</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($popularFasilitas as $fasilitas)
                            @php $isChecked = in_array($fasilitas, $selectedFasilitas, true); @endphp
                            <label class="cursor-pointer">
                                <input type="checkbox" name="fasilitas[]" value="{{ $fasilitas }}" class="peer sr-only" @checked($isChecked)>
                                <span class="inline-block rounded-full border px-2.5 py-1 text-xs font-semibold transition
                                    {{ $isChecked ? 'border-green-600 bg-green-600 text-white' : 'border-green-200 bg-green-50 text-green-800' }}">
                                    {{ $fasilitas }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-lime-500 to-green-500 px-4 py-2 text-sm font-bold text-white shadow-md shadow-green-400/30 hover:from-lime-400 hover:to-green-400">
                        Terapkan
                    </button>
                    @if ($search || $selectedKategori || count($selectedFasilitas) > 0)
                        <a href="{{ route('tamans.map') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="border-t border-gray-100 px-5 py-4">
                <p class="mb-2 text-xs font-semibold text-gray-600">Legenda pin</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Taman::kategoriPinLegend() as $kat => $color)
                        <button type="button"
                                class="map-kategori-toggle inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 transition hover:bg-lime-50"
                                data-kategori="{{ $kat }}"
                                data-active="true">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white" style="background:{{ $color }}"></span>
                            {{ $kat }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-100 px-5 py-4">
                <button type="button" id="map-locate-me"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100">
                    📍 Lokasi Saya
                </button>
            </div>
        </aside>

        <div class="overflow-hidden rounded-3xl border border-green-100 bg-white shadow-xl shadow-green-100/50">
            @if ($tamans->isEmpty())
                <div class="flex h-[480px] flex-col items-center justify-center p-8 text-center">
                    <p class="text-5xl">🗺️</p>
                    <p class="mt-4 font-bold text-gray-800">Tidak ada taman dengan koordinat untuk filter ini.</p>
                    <a href="{{ route('tamans.map') }}" class="mt-3 text-sm font-bold text-green-700 hover:underline">Reset filter</a>
                </div>
            @else
                <div class="taman-map-shell">
                    <div id="taman-map" class="taman-map-canvas" role="img" aria-label="Peta interaktif taman di Kota Batam"></div>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($tamans->isNotEmpty())
    @push('styles')
        @include('tamans.partials.leaflet-fix-styles')
        <style>
            .taman-map-popup { min-width: 220px; max-width: 260px; }
            .taman-map-popup img { width: 100%; height: 100px; object-fit: cover; border-radius: 10px; margin-bottom: 8px; }
            .taman-map-popup .popup-kategori { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px; margin-bottom: 6px; }
            .taman-map-popup .popup-fasilitas { display: flex; flex-wrap: wrap; gap: 4px; margin: 8px 0; }
            .taman-map-popup .popup-fasilitas span { font-size: 10px; background: #ecfdf5; color: #166534; padding: 2px 6px; border-radius: 999px; }
            .taman-map-popup .popup-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
            .taman-map-popup .popup-actions a { font-size: 11px; font-weight: 700; padding: 6px 10px; border-radius: 8px; text-decoration: none; }
            .taman-map-popup .popup-detail { background: #16a34a; color: white; }
            .taman-map-popup .popup-route { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
            .map-kategori-toggle[data-active="false"] { opacity: 0.45; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            onPageReady(function () {
                const mapElement = document.getElementById('taman-map');
                if (!mapElement) {
                    return;
                }

                if (typeof L === 'undefined') {
                    mapElement.innerHTML = '<div class="flex h-full items-center justify-center p-6 text-center text-sm text-gray-600">Peta tidak dapat dimuat. Periksa koneksi internet lalu muat ulang halaman.</div>';
                    return;
                }

                const points = @json($mapPoints);
                const batamCenter = [1.0456, 104.0305];
                const batamBounds = L.latLngBounds([0.8, 103.8], [1.3, 104.5]);
                let map = null;
                let markerLayers = [];
                let userLat = null;
                let userLng = null;
                let userMarker = null;

                function refreshMapSize() {
                    if (map) {
                        map.invalidateSize({ animate: false, pan: false });
                    }
                }

                function initMap() {
                    if (mapElement.offsetHeight < 200 || mapElement.offsetWidth < 200) {
                        requestAnimationFrame(initMap);
                        return;
                    }

                    map = L.map(mapElement, {
                        preferCanvas: false,
                    }).setView(batamCenter, 12);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap',
                        maxZoom: 19,
                    }).addTo(map);

                    refreshMapSize();
                    setupMarkers();
                    bindControls();
                    scheduleResizePasses();
                }

                function createPinIcon(color) {
                    return L.divIcon({
                        className: '',
                        html: '<div style="width:24px;height:24px;background:' + color + ';border:2px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 6px rgba(0,0,0,0.25)"></div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 24],
                        popupAnchor: [0, -24],
                    });
                }

                const userIcon = L.divIcon({
                    className: '',
                    html: '<div style="width:14px;height:14px;background:#2563eb;border:2px solid white;border-radius:50%;box-shadow:0 0 0 2px #2563eb55"></div>',
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                });

                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                function buildPopup(point) {
                    const fasilitasHtml = (point.fasilitas || []).slice(0, 4).map(function (f) {
                        return '<span>' + escapeHtml(f) + '</span>';
                    }).join('');

                    const routeUrl = userLat !== null
                        ? 'https://www.google.com/maps/dir/?api=1&origin=' + userLat + ',' + userLng + '&destination=' + point.lat + ',' + point.lng
                        : point.directionsUrl;

                    return `
                        <div class="taman-map-popup">
                            ${point.foto ? '<img src="' + escapeHtml(point.foto) + '" alt="">' : ''}
                            <span class="popup-kategori" style="background:${point.pinColor}22;color:${point.pinColor}">${escapeHtml(point.kategori)}</span>
                            <strong>${escapeHtml(point.nama)}</strong>
                            ${point.alamat ? '<p style="font-size:11px;color:#666;margin:4px 0 0">' + escapeHtml(point.alamat) + '</p>' : ''}
                            ${fasilitasHtml ? '<div class="popup-fasilitas">' + fasilitasHtml + '</div>' : ''}
                            <div class="popup-actions">
                                <a class="popup-detail" href="${point.url}">Detail →</a>
                                <a class="popup-route" href="${routeUrl}" target="_blank" rel="noopener">🗺️ Rute ke sini</a>
                            </div>
                        </div>`;
                }

                function kategoriVisible(kategori) {
                    const toggle = document.querySelector('.map-kategori-toggle[data-kategori="' + kategori + '"]');
                    return !toggle || toggle.dataset.active !== 'false';
                }

                function applyMarkerVisibility() {
                    let visible = 0;

                    markerLayers.forEach(function (entry) {
                        const show = kategoriVisible(entry.point.kategori);
                        if (show) {
                            if (!map.hasLayer(entry.marker)) {
                                entry.marker.addTo(map);
                            }
                            visible++;
                        } else if (map.hasLayer(entry.marker)) {
                            map.removeLayer(entry.marker);
                        }
                    });

                    const countEl = document.getElementById('map-visible-count');
                    if (countEl) {
                        countEl.textContent = visible + ' taman ditampilkan';
                    }
                }

                function setupMarkers() {
                    points.forEach(function (point) {
                        const marker = L.marker([point.lat, point.lng], {
                            icon: createPinIcon(point.pinColor),
                        }).bindPopup(buildPopup(point));

                        marker.on('popupopen', function () {
                            marker.setPopupContent(buildPopup(point));
                        });

                        markerLayers.push({ point: point, marker: marker });
                        marker.addTo(map);
                    });

                    refreshMapSize();

                    if (markerLayers.length > 0) {
                        const bounds = L.latLngBounds(markerLayers.map(function (entry) {
                            return [entry.point.lat, entry.point.lng];
                        }));

                        if (bounds.isValid()) {
                            const padded = bounds.pad(0.12);
                            if (batamBounds.contains(padded)) {
                                map.fitBounds(padded, { padding: [40, 40], maxZoom: 14 });
                            } else {
                                map.setView(batamCenter, 12);
                            }
                        }
                    }

                    refreshMapSize();
                }

                function bindControls() {
                    document.querySelectorAll('.map-kategori-toggle').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const isActive = btn.dataset.active !== 'false';
                            btn.dataset.active = isActive ? 'false' : 'true';
                            applyMarkerVisibility();
                        });
                    });

                    document.getElementById('map-locate-me')?.addEventListener('click', function () {
                        if (!navigator.geolocation) {
                            alert('Browser tidak mendukung geolokasi.');
                            return;
                        }
                        navigator.geolocation.getCurrentPosition(function (pos) {
                            userLat = pos.coords.latitude;
                            userLng = pos.coords.longitude;

                            if (userMarker) {
                                map.removeLayer(userMarker);
                            }

                            userMarker = L.marker([userLat, userLng], { icon: userIcon })
                                .addTo(map)
                                .bindPopup('Lokasi Anda')
                                .openPopup();

                            map.setView([userLat, userLng], 14);
                            refreshMapSize();
                        }, function () {
                            alert('Tidak dapat mengakses lokasi Anda.');
                        });
                    });
                }

                function scheduleResizePasses() {
                    refreshMapSize();
                    requestAnimationFrame(function () {
                        refreshMapSize();
                        requestAnimationFrame(refreshMapSize);
                    });
                    setTimeout(refreshMapSize, 150);
                    setTimeout(refreshMapSize, 500);
                    window.addEventListener('load', refreshMapSize);
                    window.addEventListener('resize', refreshMapSize);

                    if (typeof ResizeObserver !== 'undefined') {
                        new ResizeObserver(refreshMapSize).observe(mapElement.closest('.taman-map-shell') || mapElement);
                    }
                }

                initMap();
            });
        </script>
    @endpush
@endif
