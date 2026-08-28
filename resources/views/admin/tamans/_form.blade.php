@php
    $taman = $taman ?? null;
@endphp

<div class="space-y-6">
    <x-admin.rth-section
        title="Identitas RTH / Taman"
        description="Data dasar profil taman untuk database {{ config('app.name') }}. Kolom bertanda * wajib diisi.">
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <x-admin.form.label for="nama_taman" :required="true">Nama</x-admin.form.label>
                <x-admin.form.input name="nama_taman" id="nama_taman"
                                    value="{{ old('nama_taman', $taman?->nama_taman ?? '') }}"
                                    placeholder="Contoh: Taman Melati"
                                    required />
            </div>

            <div>
                <x-admin.form.label for="kategori" :required="true">Kategori</x-admin.form.label>
                <x-admin.form.select name="kategori" id="kategori" placeholder="Pilih kategori" required>
                    @foreach (\App\Models\Taman::KATEGORI as $kategori)
                        <option value="{{ $kategori }}" @selected(old('kategori', $taman?->kategori ?? '') === $kategori)>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </x-admin.form.select>
            </div>

            <div>
                <x-admin.form.label for="luasan">Luasan (M²)</x-admin.form.label>
                <x-admin.form.input type="number" name="luasan" id="luasan" min="0" step="1"
                                    value="{{ old('luasan', $taman?->luasan ?? '') }}"
                                    placeholder="Contoh: 5000" />
            </div>

            <div>
                <x-admin.form.label for="kelurahan_id">Kelurahan / Kecamatan</x-admin.form.label>
                <x-admin.searchable-select
                    name="kelurahan_id"
                    id="kelurahan_id"
                    :selected="old('kelurahan_id', $taman?->kelurahan_id ?? '')"
                    :selected-label="$selectedKelurahanLabel ?? ''"
                    :options="$kelurahanOptions ?? []"
                    placeholder="Pilih kelurahan atau kecamatan..."
                    empty-text="Kelurahan tidak ditemukan. Jalankan seeder wilayah terlebih dahulu."
                />
            </div>

            <div class="md:col-span-2">
                <x-admin.form.label for="alamat">Alamat</x-admin.form.label>
                <x-admin.form.textarea name="alamat" id="alamat" rows="2"
                                       placeholder="Contoh: Jl. Melati No. 1, Batam Kota">{{ old('alamat', $taman?->alamat ?? '') }}</x-admin.form.textarea>
            </div>

            <div class="md:col-span-2">
                <x-admin.form.label for="deskripsi">Deskripsi</x-admin.form.label>
                <x-admin.form.textarea name="deskripsi" id="deskripsi" rows="4"
                                       placeholder="Uraikan profil, fungsi, dan keunggulan taman...">{{ old('deskripsi', $taman?->deskripsi ?? '') }}</x-admin.form.textarea>
            </div>
        </div>
    </x-admin.rth-section>

    <x-admin.rth-section
        title="Koordinat Lokasi"
        description="Latitude & longitude untuk peta dan deteksi wilayah otomatis. Default: pusat Kota Batam.">
        @php
            $defaultCoordinates = \App\Models\Taman::defaultCoordinates();
            $latitudeValue = old('latitude', filled($taman?->latitude) ? $taman->latitude : $defaultCoordinates['latitude']);
            $longitudeValue = old('longitude', filled($taman?->longitude) ? $taman->longitude : $defaultCoordinates['longitude']);
        @endphp
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <x-admin.form.label for="latitude">Latitude</x-admin.form.label>
                <x-admin.form.input name="latitude" id="latitude"
                                    value="{{ $latitudeValue }}"
                                    placeholder="Contoh: {{ $defaultCoordinates['latitude'] }}" />
            </div>

            <div>
                <x-admin.form.label for="longitude">Longitude</x-admin.form.label>
                <x-admin.form.input name="longitude" id="longitude"
                                    value="{{ $longitudeValue }}"
                                    placeholder="Contoh: {{ $defaultCoordinates['longitude'] }}" />
            </div>

            <div class="md:col-span-2">
                <x-admin.form.label>Pilih Lokasi di Peta</x-admin.form.label>
                <p class="mb-2 text-sm text-gray-500">Peta default menampilkan Kota Batam. Klik peta atau geser marker untuk menyesuaikan lokasi. Kelurahan/kecamatan akan terisi otomatis jika terdeteksi.</p>
                <div id="wilayah-resolve-notice" class="mb-2 hidden rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"></div>
                <div id="taman-location-map" class="overflow-hidden rounded-lg border border-gray-300 bg-gray-200"></div>
            </div>
        </div>
    </x-admin.rth-section>

    <x-admin.rth-section
        title="Galeri Foto Taman"
        description="Unggah foto landscape sesuai patokan agar tampilan publik rapi.">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
            <p class="font-medium text-gray-900">Patokan upload foto</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-gray-600">
                <li>Rasio aspek wajib: <strong class="text-gray-900">{{ \App\Models\Taman::galleryAspectRatioLabel() }}</strong> (landscape)</li>
                <li>Ukuran disarankan: <strong class="text-gray-900">{{ \App\Models\Taman::GALLERY_RECOMMENDED_WIDTH }} × {{ \App\Models\Taman::GALLERY_RECOMMENDED_HEIGHT }} px</strong></li>
                <li>Minimal sisi terpendek: <strong class="text-gray-900">{{ \App\Models\Taman::GALLERY_MIN_SHORT_SIDE }} px</strong></li>
                <li>Format: JPG / PNG — maks. {{ \App\Models\Taman::GALLERY_MAX_SIZE_KB / 1024 }} MB per foto</li>
                <li>Minimal 1 foto agar status data Lengkap</li>
            </ul>
        </div>

        <div class="mt-5">
            <x-admin.form.label for="fotos">Upload Foto Baru</x-admin.form.label>
            <input type="file" name="fotos[]" id="fotos" accept="image/jpeg,image/png,image/webp" multiple
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-emerald-800 hover:file:bg-emerald-100">
            <p class="mt-1 text-sm text-gray-500">Pilih satu atau lebih foto sesuai patokan di atas.</p>
            <div id="foto-preview" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"></div>
        </div>

        @if ($taman?->images?->isNotEmpty())
            <div class="mt-5 border-t border-gray-100 pt-5">
                <x-admin.form.label>Foto Galeri Saat Ini</x-admin.form.label>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($taman->images as $image)
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <div class="overflow-hidden">
                                <img src="{{ $image->url }}" alt="Foto {{ $taman->nama_taman }}"
                                     class="aspect-[16/9] w-full object-cover object-center">
                            </div>
                            <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-2 py-1.5">
                                <span class="truncate text-xs text-gray-500">Foto galeri</span>
                                <button type="button"
                                        onclick="if(confirm('Hapus foto ini?')) document.getElementById('delete-image-{{ $image->id }}').submit()"
                                        class="shrink-0 rounded-md bg-red-600 px-2 py-0.5 text-xs font-medium text-white hover:bg-red-700">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-admin.rth-section>

    <x-admin.rth-section
        title="Data Pembangunan"
        description="Informasi historis pembangunan RTH/taman.">
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <x-admin.form.label for="tahun_pembangunan">Tahun Pembangunan</x-admin.form.label>
                <x-admin.form.input type="number" name="tahun_pembangunan" id="tahun_pembangunan"
                                    min="1950" max="{{ date('Y') }}"
                                    value="{{ old('tahun_pembangunan', $taman?->tahun_pembangunan ?? '') }}"
                                    placeholder="Contoh: 2018" />
            </div>

            <div>
                <x-admin.form.label for="nilai_pembangunan">Nilai Pembangunan (Rp)</x-admin.form.label>
                <x-admin.form.input type="number" name="nilai_pembangunan" id="nilai_pembangunan" min="0" step="1"
                                    value="{{ old('nilai_pembangunan', $taman?->nilai_pembangunan ?? '') }}"
                                    placeholder="Contoh: 1500000000" />
            </div>

            <div>
                <x-admin.form.label for="kontraktor">Kontraktor</x-admin.form.label>
                <x-admin.form.input name="kontraktor" id="kontraktor"
                                    value="{{ old('kontraktor', $taman?->kontraktor ?? '') }}"
                                    placeholder="Contoh: PT Pembangun Jaya" />
            </div>

            <div>
                <x-admin.form.label for="konsultan_perencana">Konsultan Perencana</x-admin.form.label>
                <x-admin.form.input name="konsultan_perencana" id="konsultan_perencana"
                                    value="{{ old('konsultan_perencana', $taman?->konsultan_perencana ?? '') }}"
                                    placeholder="Contoh: Konsultan Perencana ABC" />
            </div>
        </div>
    </x-admin.rth-section>

    @include('admin.tamans.partials.fasilitas-fields')

    <x-admin.rth-section
        title="Pemutakhiran Data"
        description="Tentukan waktu pemutakhiran data profil taman.">
        <x-slot:actions>
            @if ($taman)
                <x-admin.taman-status-data-badge :status="$taman->status_data" size="lg" />
            @endif
        </x-slot:actions>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <x-admin.form.label for="data_verified_at">Waktu Pemutakhiran</x-admin.form.label>
                <x-admin.form.input
                    type="datetime-local"
                    name="data_verified_at"
                    id="data_verified_at"
                    value="{{ old('data_verified_at', ($taman?->data_verified_at ?? now())->timezone(config('app.timezone'))->format('Y-m-d\TH:i')) }}" />
                <p class="mt-1 text-xs text-gray-500">Kosongkan untuk memakai waktu saat ini. Isi jika data profil sudah diverifikasi pada tanggal tertentu.</p>
            </div>

            @if ($taman)
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-sm font-medium text-gray-700">Terakhir Diubah Sistem</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $taman->updated_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                </div>
            @endif
        </div>
    </x-admin.rth-section>
</div>

<x-admin.rth-form-actions>
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ $taman ? route('admin.tamans.show', $taman) : route('admin.tamans.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</x-admin.rth-form-actions>
