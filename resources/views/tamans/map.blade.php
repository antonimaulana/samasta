@extends('layouts.public')

@section('body_class', 'bg-[#f0fdf4]')
@section('title', 'Peta Taman')

@section('meta_description', 'Peta interaktif taman hijau Kota Batam — filter kategori, lihat foto & fasilitas, dan arahkan rute menuju taman.')

@section('content')
    @php
        $navActionClass = 'inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-3 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50 sm:flex-none sm:px-4';
    @endphp
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-emerald-700 hover:underline">
            ← Kembali ke beranda
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
        <aside class="h-fit overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm lg:sticky lg:top-24">
            <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-green-800">Filter Peta</h2>
                        <p class="mt-1 text-sm text-green-700/80">Filter taman yang ditampilkan di peta</p>
                    </div>
                    <div id="map-visible-count"
                         class="{{ $navActionClass }} w-full whitespace-nowrap sm:max-w-[11rem] sm:flex-none"
                         aria-live="polite">
                        {{ $tamans->count() }} taman ditampilkan
                    </div>
                </div>
            </div>
            <form action="{{ route('tamans.map') }}" method="GET" class="space-y-5 p-6">
                <div>
                    <label for="search" class="mb-2 block text-sm font-medium text-gray-700">Nama Taman</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-green-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                            </svg>
                        </span>
                        <input type="search"
                               name="search"
                               id="search"
                               value="{{ $search }}"
                               placeholder="Contoh: Taman Majapahit, Taman KDA..."
                               class="w-full rounded-xl border border-gray-200 py-3 pl-10 pr-4 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20">
                    </div>
                </div>
                <div>
                    <label for="kategori" class="mb-2 block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="kategori"
                            id="kategori"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20">
                        <option value="">Semua kategori</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori }}" @selected($selectedKategori === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                @include('tamans.partials.fasilitas-filter-chips', [
                    'popularFasilitas' => $popularFasilitas,
                    'selectedFasilitas' => $selectedFasilitas,
                ])
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1 sm:justify-start">
                    <button type="submit"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                        </svg>
                        Terapkan
                    </button>
                    @if ($search || $selectedKategori || count($selectedFasilitas) > 0)
                        <a href="{{ route('tamans.map') }}"
                           class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 sm:flex-none">
                            Reset Filter
                        </a>
                    @endif
                </div>
            </form>

            <div class="border-t border-green-50 px-6 py-4">
                <p class="mb-2 text-sm font-medium text-gray-700">Legenda pin</p>
                <div class="grid grid-cols-2 gap-1.5 sm:flex sm:flex-wrap sm:gap-2">
                    @foreach (\App\Models\Taman::kategoriPinLegend() as $kat => $color)
                        <button type="button"
                                class="map-kategori-toggle inline-flex w-full items-center justify-center gap-1.5 rounded-full border px-2 py-1 text-xs font-bold transition hover:opacity-90 sm:w-auto sm:px-2.5 sm:py-1 sm:text-xs"
                                style="{{ \App\Models\Taman::kategoriBadgeInlineStyle($kat) }}"
                                data-kategori="{{ $kat }}"
                                data-active="true">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white/80" style="background:{{ $color }}"></span>
                            <span class="truncate">{{ $kat }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-green-50 px-6 py-4">
                <button type="button"
                        id="map-locate-me"
                        class="{{ $navActionClass }} w-full sm:w-full">
                    @include('tamans.partials.icon-location-outline')
                    Lokasi Saya
                </button>
            </div>
        </aside>

        <div class="overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
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
            .taman-map-popup .popup-kategori { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px; margin-bottom: 6px; border: 1px solid transparent; }
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
                            <span class="popup-kategori" style="background:${point.pinColor}22;color:${point.pinColor};border-color:${point.pinColor}44">${escapeHtml(point.kategori)}</span>
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

@include('tamans.partials.fasilitas-filter-script')
