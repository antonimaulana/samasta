@extends('layouts.public')

@section('body_class', 'bg-[#f0fdf4]')
@section('title', $taman->nama_taman)

@section('meta_description', 'Profil '.$taman->nama_taman.' — '.$taman->kategori.' di Kota Batam. Lihat fasilitas, foto, dan navigasi menuju taman.')

@section('content')
    <div class="mb-6">
        <a href="{{ route('tamans.index') }}"
           class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-semibold text-green-700 transition hover:bg-lime-100">
            ← Kembali ke daftar taman
        </a>
    </div>

    @php
        $mapCoords = $taman->normalizedMapCoordinates();
        $parkLat = $mapCoords['lat'] ?? null;
        $parkLng = $mapCoords['lng'] ?? null;
    @endphp

    <article class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-green-100/60 ring-1 ring-green-100">
        <x-taman-gallery :taman="$taman" variant="public" nested :constrained="false" />

        <div class="p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-green-900 sm:text-4xl">{{ $taman->nama_taman }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-gradient-to-r from-lime-100 to-green-100 px-3 py-1 text-sm font-bold text-green-800 ring-1 ring-green-200">
                            {{ $taman->kategori }}
                        </span>
                        @if ($taman->luasan > 0)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800 ring-1 ring-emerald-100">
                                {{ number_format($taman->luasan, 0, ',', '.') }} M²
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('aduan.create', ['taman' => $taman->id]) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-bold text-orange-800 shadow-sm transition hover:bg-orange-100">
                        📢 Laporkan kondisi
                    </a>
                    <a href="{{ route('survey.create', ['taman' => $taman->id, 'kategori' => 'Kondisi Taman']) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-bold text-violet-800 shadow-sm transition hover:bg-violet-100">
                        ⭐ Beri penilaian
                    </a>
                </div>
            </div>

            <div class="mt-5 flex items-start gap-2 rounded-2xl bg-green-50/80 px-4 py-3 text-gray-700 ring-1 ring-green-100">
                <span class="mt-0.5 text-lg">📍</span>
                <p class="text-sm leading-relaxed sm:text-base">{{ $taman->alamat }}</p>
            </div>

            @if ($taman->deskripsi)
                <div class="mt-8">
                    <h2 class="text-lg font-bold text-gray-900">Deskripsi</h2>
                    <p class="mt-2 whitespace-pre-line leading-relaxed text-gray-600">{{ $taman->deskripsi }}</p>
                </div>
            @endif

            @if ($taman->fasilitas_items !== [])
                <div class="mt-8">
                    <h2 class="text-lg font-bold text-gray-900">Fasilitas</h2>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($taman->fasilitas_items as $item)
                            @php
                                $kondisiClass = match ($item['kondisi']) {
                                    'Baik' => 'bg-green-100 text-green-800 ring-green-200',
                                    'Rusak Ringan' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                    default => 'bg-red-100 text-red-800 ring-red-200',
                                };
                            @endphp
                            <li class="flex items-center justify-between gap-2 rounded-xl bg-lime-50 px-3 py-2.5 text-sm font-medium text-green-800 ring-1 ring-lime-100">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-[10px] text-white">✓</span>
                                    {{ $item['nama'] }}
                                </span>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 {{ $kondisiClass }}">
                                    {{ $item['kondisi'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($parkLat && $parkLng)
                <div class="mt-8 border-t border-green-100 pt-8">
                    <h2 class="text-lg font-bold text-gray-900">Lokasi & Navigasi</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $taman->latitude }}, {{ $taman->longitude }}</p>

                    <div id="nav-distance-panel" class="mt-4 hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold text-emerald-700">Jarak dari Anda</p>
                                <p id="nav-distance-text" class="text-lg font-black text-emerald-900">—</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-emerald-700">Estimasi tempuh</p>
                                <p id="nav-duration-text" class="text-lg font-black text-emerald-900">—</p>
                                <p class="text-[10px] text-emerald-600">*estimasi kendaraan ~30 km/j</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" id="nav-calc-distance"
                                data-lat="{{ $parkLat }}"
                                data-lng="{{ $parkLng }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100">
                            📍 Hitung jarak dari lokasi saya
                        </button>
                        <a id="nav-google-link"
                           href="{{ \App\Support\ParkNavigation::googleDirectionsUrl($parkLat, $parkLng) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-lime-500 to-green-500 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-green-400/30 transition hover:from-lime-400 hover:to-green-400">
                            🗺️ Rute Google Maps
                        </a>
                        <a href="{{ \App\Support\ParkNavigation::wazeUrl($parkLat, $parkLng) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 rounded-xl border border-blue-300 bg-blue-50 px-4 py-2.5 text-sm font-bold text-blue-800 transition hover:bg-blue-100">
                            Waze
                        </a>
                    </div>

                    <div id="map" class="taman-detail-map z-0 mt-4 overflow-hidden rounded-2xl border border-green-100 ring-1 ring-green-50"></div>
                </div>
            @endif
        </div>
    </article>
@endsection

@if ($parkLat && $parkLng)
    @push('styles')
        @include('tamans.partials.leaflet-fix-styles')
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            onPageReady(function () {
                const mapElement = document.getElementById('map');
                if (!mapElement || typeof L === 'undefined') {
                    return;
                }

                const lat = {{ $parkLat }};
                const lng = {{ $parkLng }};
                const namaTaman = @json($taman->nama_taman);
                const pinColor = @json(\App\Models\Taman::kategoriPinColor($taman->kategori));

                const map = L.map('map').setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                }).addTo(map);

                const pinIcon = L.divIcon({
                    className: '',
                    html: '<div style="width:28px;height:28px;background:' + pinColor + ';border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,0.25)"></div>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 28],
                    popupAnchor: [0, -28],
                });

                L.marker([lat, lng], { icon: pinIcon })
                    .addTo(map)
                    .bindPopup('<strong>' + namaTaman + '</strong>')
                    .openPopup();

                setTimeout(function () { map.invalidateSize(); }, 100);

                const calcBtn = document.getElementById('nav-calc-distance');
                const panel = document.getElementById('nav-distance-panel');
                const distanceText = document.getElementById('nav-distance-text');
                const durationText = document.getElementById('nav-duration-text');
                const googleLink = document.getElementById('nav-google-link');

                calcBtn?.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        alert('Browser tidak mendukung geolokasi.');
                        return;
                    }

                    calcBtn.disabled = true;
                    calcBtn.textContent = 'Mengambil lokasi...';

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const userLat = position.coords.latitude;
                            const userLng = position.coords.longitude;
                            const distanceKm = haversineKm(userLat, userLng, lat, lng);
                            const minutes = Math.max(1, Math.round((distanceKm / 30) * 60));

                            panel?.classList.remove('hidden');
                            if (distanceText) {
                                distanceText.textContent = distanceKm < 1
                                    ? Math.round(distanceKm * 1000) + ' m'
                                    : distanceKm.toFixed(1).replace('.', ',') + ' km';
                            }
                            if (durationText) {
                                durationText.textContent = minutes + ' menit';
                            }
                            if (googleLink) {
                                googleLink.href = 'https://www.google.com/maps/dir/?api=1&origin='
                                    + userLat + ',' + userLng + '&destination=' + lat + ',' + lng;
                            }

                            calcBtn.disabled = false;
                            calcBtn.textContent = '📍 Perbarui jarak';
                        },
                        function () {
                            alert('Tidak dapat mengakses lokasi Anda.');
                            calcBtn.disabled = false;
                            calcBtn.textContent = '📍 Hitung jarak dari lokasi saya';
                        },
                        { enableHighAccuracy: true, timeout: 15000 }
                    );
                });

                function haversineKm(lat1, lng1, lat2, lng2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLng = (lng2 - lng1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) ** 2
                        + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                }
            });
        </script>
    @endpush
@endif
