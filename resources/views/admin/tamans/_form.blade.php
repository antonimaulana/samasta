@php
    $taman = $taman ?? null;
    $fasilitasText = old('fasilitas', $taman?->fasilitas ? implode("\n", $taman->fasilitas) : '');
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="nama_taman" class="mb-1 block text-sm font-medium text-gray-700">Nama Taman *</label>
        <input type="text" name="nama_taman" id="nama_taman"
               value="{{ old('nama_taman', $taman?->nama_taman ?? '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="kategori" class="mb-1 block text-sm font-medium text-gray-700">Kategori / Klasifikasi *</label>
        <select name="kategori" id="kategori" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @foreach (\App\Models\Taman::KATEGORI as $kategori)
                <option value="{{ $kategori }}" @selected(old('kategori', $taman?->kategori ?? 'Taman Kota') === $kategori)>
                    {{ $kategori }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="luasan" class="mb-1 block text-sm font-medium text-gray-700">Luasan (M²) *</label>
        <input type="number" name="luasan" id="luasan" min="1" step="1" required
               value="{{ old('luasan', $taman?->luasan ?? '') }}"
               placeholder="Contoh: 5000"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <p class="mt-1 text-xs text-gray-500">Luas area taman dalam meter persegi.</p>
    </div>

    <div class="md:col-span-2">
        <label for="fotos" class="mb-1 block text-sm font-medium text-gray-700">Galeri Foto Taman</label>
        <input type="file" name="fotos[]" id="fotos" accept="image/*" multiple
               class="w-full rounded-lg border border-gray-300 px-3 py-2 file:mr-3 file:rounded file:border-0 file:bg-green-50 file:px-3 file:py-1 file:text-green-700">
        <p class="mt-1 text-xs text-gray-500">Pilih satu atau lebih foto. Format: JPG, PNG. Maks. 2 MB per foto.</p>

        <div id="foto-preview" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"></div>
    </div>

    @if ($taman?->images?->isNotEmpty())
        <div class="md:col-span-2">
            <p class="mb-3 text-sm font-medium text-gray-700">Foto Galeri Saat Ini</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($taman->images as $image)
                    <div class="group relative overflow-hidden rounded-lg border border-gray-200">
                        <img src="{{ $image->url }}" alt="Foto {{ $taman->nama_taman }}"
                             class="h-28 w-full object-cover">
                        <button type="button"
                                onclick="if(confirm('Hapus foto ini?')) document.getElementById('delete-image-{{ $image->id }}').submit()"
                                class="absolute right-1 top-1 rounded bg-red-600 px-2 py-0.5 text-xs font-medium text-white opacity-90 hover:bg-red-700">
                            Hapus
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="md:col-span-2">
        <label for="kelurahan_id" class="mb-1 block text-sm font-medium text-gray-700">Kelurahan / Kecamatan</label>
        <x-admin.searchable-select
            name="kelurahan_id"
            id="kelurahan_id"
            :selected="old('kelurahan_id', $taman?->kelurahan_id ?? '')"
            :selected-label="$selectedKelurahanLabel ?? ''"
            :options="$kelurahanOptions ?? []"
            placeholder="Ketik kecamatan atau kelurahan..."
            empty-text="Kelurahan tidak ditemukan. Jalankan seeder wilayah terlebih dahulu."
        />
        <p class="mt-1 text-xs text-gray-500">Pilih manual atau biarkan terisi otomatis saat koordinat dipilih di peta.</p>
    </div>

    <div class="md:col-span-2">
        <label for="alamat" class="mb-1 block text-sm font-medium text-gray-700">Alamat *</label>
        <textarea name="alamat" id="alamat" rows="2" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('alamat', $taman?->alamat ?? '') }}</textarea>
    </div>

    <div>
        <label for="latitude" class="mb-1 block text-sm font-medium text-gray-700">Latitude</label>
        <input type="text" name="latitude" id="latitude"
               value="{{ old('latitude', $taman?->latitude ?? '') }}"
               placeholder="-6.12345678"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="longitude" class="mb-1 block text-sm font-medium text-gray-700">Longitude</label>
        <input type="text" name="longitude" id="longitude"
               value="{{ old('longitude', $taman?->longitude ?? '') }}"
               placeholder="106.12345678"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Pilih Lokasi di Peta</label>
        <p class="mb-2 text-xs text-gray-500">Klik pada peta atau geser marker untuk mengatur koordinat. Kelurahan/kecamatan akan diisi otomatis jika terdeteksi.</p>
        <div id="wilayah-resolve-notice" class="mb-2 hidden rounded-lg border px-3 py-2 text-xs"></div>
        <div id="map" style="height: 350px;" class="z-0 rounded-lg border border-gray-300"></div>
    </div>

    <div class="md:col-span-2">
        <label for="deskripsi" class="mb-1 block text-sm font-medium text-gray-700">Deskripsi *</label>
        <textarea name="deskripsi" id="deskripsi" rows="4" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('deskripsi', $taman?->deskripsi ?? '') }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label for="fasilitas" class="mb-1 block text-sm font-medium text-gray-700">Fasilitas</label>
        <textarea name="fasilitas" id="fasilitas" rows="4"
                  placeholder="Satu fasilitas per baris, contoh:&#10;Area bermain anak&#10;Jogging track&#10;Toilet umum"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ $fasilitasText }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Pisahkan setiap fasilitas dengan baris baru atau koma.</p>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.tamans.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaultLat = -1.08286;
            const defaultLng = 104.03050;

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const kelurahanHidden = document.getElementById('kelurahan_id');
            const kelurahanRoot = kelurahanHidden?.closest('[data-searchable-select]');
            const wilayahNotice = document.getElementById('wilayah-resolve-notice');
            const resolveUrl = @json(route('admin.tamans.resolve-wilayah'));
            let resolveTimer = null;

            const initialLat = latInput.value ? parseFloat(latInput.value) : defaultLat;
            const initialLng = lngInput.value ? parseFloat(lngInput.value) : defaultLng;

            const map = L.map('map').setView([initialLat, initialLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(map);

            let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                scheduleWilayahResolve(lat, lng);
            }

            function showWilayahNotice(message, success) {
                if (! wilayahNotice) {
                    return;
                }

                wilayahNotice.textContent = message;
                wilayahNotice.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-900', 'border-amber-200', 'bg-amber-50', 'text-amber-900');

                if (success) {
                    wilayahNotice.classList.add('border-green-200', 'bg-green-50', 'text-green-900');
                } else {
                    wilayahNotice.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-900');
                }
            }

            function scheduleWilayahResolve(lat, lng) {
                clearTimeout(resolveTimer);
                resolveTimer = setTimeout(function () {
                    resolveWilayah(lat, lng);
                }, 400);
            }

            async function resolveWilayah(lat, lng) {
                if (! kelurahanRoot) {
                    return;
                }

                try {
                    const response = await fetch(`${resolveUrl}?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (! response.ok) {
                        return;
                    }

                    const data = await response.json();

                    if (data.resolved && data.kelurahan_id) {
                        kelurahanRoot.dispatchEvent(new CustomEvent('searchable-select:set-value', {
                            bubbles: true,
                            detail: {
                                value: String(data.kelurahan_id),
                                label: data.label || '',
                            },
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

            if (!latInput.value || !lngInput.value) {
                updateInputs(initialLat, initialLng);
            } else {
                scheduleWilayahResolve(initialLat, initialLng);
            }

            setTimeout(function () {
                map.invalidateSize();
            }, 100);

            const fotosInput = document.getElementById('fotos');
            const previewContainer = document.getElementById('foto-preview');

            if (fotosInput && previewContainer) {
                fotosInput.addEventListener('change', function () {
                    previewContainer.innerHTML = '';

                    Array.from(this.files).forEach(function (file) {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function (event) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'overflow-hidden rounded-lg border border-green-200 bg-green-50';

                            const img = document.createElement('img');
                            img.src = event.target.result;
                            img.alt = file.name;
                            img.className = 'h-28 w-full object-cover';

                            const caption = document.createElement('p');
                            caption.className = 'truncate px-2 py-1 text-xs text-gray-600';
                            caption.textContent = file.name;

                            wrapper.appendChild(img);
                            wrapper.appendChild(caption);
                            previewContainer.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }
        });
    </script>
@endpush
