@extends('layouts.public')

@section('title', 'Aduan Masyarakat')

@section('hero')
    <section class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-orange-500 to-red-500 text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-xs font-bold uppercase tracking-widest text-orange-100">Layanan Publik</p>
            <h1 class="mt-2 text-3xl font-black sm:text-4xl">Aduan Kondisi Taman</h1>
            <p class="mt-3 max-w-2xl text-white/90">
                Laporkan kondisi taman, tanaman rusak, atau tanaman berbahaya di Kota Batam.
                Tim Disperakimtan akan menindaklanjuti aduan Anda.
            </p>
        </div>
        <div class="relative -mb-1 text-[#f0fdf4]">
            <svg viewBox="0 0 1440 40" fill="currentColor" class="block w-full"><path d="M0,20 Q360,40 720,20 T1440,20 L1440,40 L0,40 Z"/></svg>
        </div>
    </section>
@endsection

@section('main_class', 'mx-auto max-w-3xl px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="relative -mt-4 pb-16">
        <p class="mb-4 text-center text-sm text-gray-600">
            Sudah punya nomor aduan?
            <a href="{{ route('aduan.check') }}" class="font-semibold text-green-700 hover:underline">Cek status di sini</a>
        </p>
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('aduan.store') }}" enctype="multipart/form-data"
              class="space-y-6 rounded-2xl border border-orange-100 bg-white p-6 shadow-lg shadow-orange-100/50 sm:p-8">
            @csrf
            <div class="hidden" aria-hidden="true">
                <label for="_website">Website</label>
                <input type="text" name="_website" id="_website" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <label for="jenis_aduan" class="mb-1 block text-sm font-semibold text-gray-700">Jenis Aduan *</label>
                <select name="jenis_aduan" id="jenis_aduan" required
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">
                    <option value="">Pilih jenis aduan</option>
                    @foreach (\App\Models\AduanMasyarakat::JENIS as $jenis)
                        <option value="{{ $jenis }}" @selected(old('jenis_aduan') === $jenis)>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="taman_id_search" class="mb-1 block text-sm font-semibold text-gray-700">Taman terkait *</label>
                <x-admin.taman-select
                    :tamans="$tamans"
                    :selected="old('taman_id', $selectedTamanId)"
                    :required="true"
                    :create-url="null"
                    empty-message="Belum ada data taman dalam sistem."
                    placeholder="Ketik nama taman untuk mencari..."
                    hint="Pilih taman yang terkait dengan aduan Anda."
                    input-class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                />
            </div>

            <div>
                <label for="deskripsi" class="mb-1 block text-sm font-semibold text-gray-700">Uraian aduan *</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" required minlength="20"
                          placeholder="Jelaskan kondisi yang Anda temukan (minimal 20 karakter)..."
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">{{ old('deskripsi') }}</textarea>
            </div>

            <div>
                <label for="foto" class="mb-1 block text-sm font-semibold text-gray-700">Foto bukti *</label>
                <input type="file" name="foto" id="foto" accept="image/*" required
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-orange-50 file:px-3 file:py-1 file:text-orange-700">
                <p class="mt-1 text-xs text-gray-500">Maks. 4 MB — foto kondisi lapangan wajib dilampirkan.</p>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Lokasi GPS *</p>
                        <p class="text-xs text-gray-500">Wajib ambil koordinat lokasi Anda saat ini sebelum mengirim aduan.</p>
                    </div>
                    <button type="button" id="aduan-gps-btn"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        Ambil Lokasi Saya
                    </button>
                </div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                <p id="aduan-gps-status" class="mt-2 text-xs text-gray-500"></p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nama_pelapor" class="mb-1 block text-sm font-semibold text-gray-700">Nama pelapor *</label>
                    <input type="text" name="nama_pelapor" id="nama_pelapor" required value="{{ old('nama_pelapor') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <div>
                    <label for="kontak_pelapor" class="mb-1 block text-sm font-semibold text-gray-700">No. HP / WhatsApp *</label>
                    <input type="tel" name="kontak_pelapor" id="kontak_pelapor" required value="{{ old('kontak_pelapor') }}"
                           placeholder="Contoh: 081234567890"
                           inputmode="tel" autocomplete="tel"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-300/40 hover:from-orange-400 hover:to-red-400">
                    Kirim Aduan
                </button>
                <a href="{{ route('home') }}"
                   class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        onPageReady(function () {
            const form = document.querySelector('form[action="{{ route('aduan.store') }}"]');
            const btn = document.getElementById('aduan-gps-btn');
            const lat = document.getElementById('latitude');
            const lng = document.getElementById('longitude');
            const status = document.getElementById('aduan-gps-status');
            const gpsBox = btn?.closest('.rounded-xl');

            function gpsReady() {
                return lat?.value && lng?.value;
            }

            function showGpsError(message) {
                if (status) {
                    status.textContent = message;
                    status.classList.add('text-red-600');
                }
                gpsBox?.classList.add('border-red-300', 'bg-red-50');
            }

            function clearGpsError() {
                status?.classList.remove('text-red-600');
                gpsBox?.classList.remove('border-red-300', 'bg-red-50');
            }

            form?.addEventListener('submit', function (event) {
                if (! gpsReady()) {
                    event.preventDefault();
                    showGpsError('Lokasi GPS wajib diambil. Klik "Ambil Lokasi Saya" dan izinkan akses lokasi.');
                    btn?.focus();
                }
            });

            if (!btn) return;

            btn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    showGpsError('Peramban tidak mendukung geolokasi.');
                    return;
                }
                clearGpsError();
                status.textContent = 'Mengambil lokasi...';
                btn.disabled = true;
                navigator.geolocation.getCurrentPosition(function (pos) {
                    lat.value = pos.coords.latitude.toFixed(8);
                    lng.value = pos.coords.longitude.toFixed(8);
                    status.textContent = 'Lokasi GPS berhasil dilampirkan.';
                    btn.disabled = false;
                }, function () {
                    showGpsError('Gagal mengambil lokasi. Izinkan akses lokasi di peramban Anda.');
                    btn.disabled = false;
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            if (gpsReady()) {
                status.textContent = 'Lokasi GPS berhasil dilampirkan.';
            }
        });
    </script>
@endpush
