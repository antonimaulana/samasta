@extends('layouts.admin')

@section('title', 'Template Dokumen DPA')
@section('header', 'Template Dokumen DPA')

@section('content')
    <p class="mb-6 text-sm text-gray-600">
        Unggah template referensi per jenis dokumen (opsional). Dokumen output dihasilkan otomatis dari form input paket pekerjaan menggunakan layout PDF standar sistem.
    </p>

    @foreach ($tahapLabels as $tahap => $label)
        <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="font-semibold text-gray-900">Tahap {{ $label }}</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($templates->get($tahap, collect()) as $template)
                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $template->nama }}</p>
                            <p class="text-xs text-gray-500">Kode: {{ $template->kode }}</p>
                            @if ($template->template_path)
                                <a href="{{ asset($template->template_path) }}" target="_blank" class="text-xs text-green-700 hover:underline">Lihat template</a>
                            @else
                                <p class="text-xs text-amber-600">Belum ada template</p>
                            @endif
                        </div>
                        <form action="{{ route('admin.dpa.document-templates.update', $template) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="file" name="template_file" required class="text-xs">
                            <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">Unggah</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection
