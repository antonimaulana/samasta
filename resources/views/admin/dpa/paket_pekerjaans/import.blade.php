@extends('layouts.admin')

@section('title', 'Import Paket Pekerjaan')
@section('header', 'Import Paket Pekerjaan (CSV)')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <a href="{{ route('admin.dpa.dpas.show', $dpa) }}" class="text-sm text-green-700 hover:text-green-900">&larr; {{ $dpa->nama_dpa }}</a>
        </div>

        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900">
            <p class="font-semibold">Import massal paket pekerjaan</p>
            <p class="mt-2">Unggah CSV berisi banyak paket pekerjaan sekaligus untuk DPA ini.</p>
            <a href="{{ route('admin.dpa.paket-pekerjaans.import.template') }}"
               class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Unduh Template CSV
            </a>
        </div>

        @if ($result = session('import_result'))
            <div class="rounded-xl border p-5 text-sm {{ ($result['imported'] ?? 0) > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                <p class="font-semibold">{{ number_format($result['imported'] ?? 0) }} paket diimport, {{ number_format($result['skipped'] ?? 0) }} dilewati.</p>
                @if (! empty($result['errors']))
                    <ul class="mt-3 max-h-48 space-y-1 overflow-y-auto text-xs">
                        @foreach ($result['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <form action="{{ route('admin.dpa.paket-pekerjaans.import.store', $dpa) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="file" class="mb-2 block text-sm font-medium text-gray-700">File CSV *</label>
                <input type="file" name="file" id="file" accept=".csv,text/csv" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-green-700">
                @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Mulai Import</button>
                    <a href="{{ route('admin.dpa.dpas.show', $dpa) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
