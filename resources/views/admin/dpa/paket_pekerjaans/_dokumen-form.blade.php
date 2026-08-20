@php
    $fields = \App\Support\DpaDocumentFields::forKode($def['kode']);
    $defaults = \App\Support\DpaDocumentFields::defaultValues($paketPekerjaan, $def['kode']);
    $savedInput = array_merge($defaults, $doc?->input_data ?? []);
    $userFields = array_values(array_filter($fields, fn ($field) => ! ($field['readonly'] ?? false)));
    $autoFields = array_values(array_filter($fields, fn ($field) => ($field['readonly'] ?? false)));
@endphp

<div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="font-medium text-gray-900">{{ $def['label'] }}</p>
            @if ($doc?->file_path)
                <div class="mt-1 flex flex-wrap gap-3 text-xs">
                    <a href="{{ asset($doc->file_path) }}" target="_blank" class="text-green-700 hover:underline">Lihat PDF</a>
                    @if ($doc->generated_at)
                        <span class="text-gray-500">Dibuat {{ $doc->generated_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            @else
                <p class="text-xs text-gray-500">Isi form sesuai template, lalu generate PDF</p>
            @endif
        </div>
    </div>

    @if ($fields !== [])
        <form action="{{ route('admin.dpa.paket-pekerjaans.dokumens.generate', $paketPekerjaan) }}" method="POST" class="mt-3 space-y-3 border-t border-gray-200 pt-3">
            @csrf
            <input type="hidden" name="tahap" value="{{ $activeTahap }}">
            <input type="hidden" name="kode_dokumen" value="{{ $def['kode'] }}">

            @foreach ($autoFields as $field)
                <input type="hidden" name="fields[{{ $field['name'] }}]" value="{{ old('fields.'.$field['name'], $savedInput[$field['name']] ?? '') }}">
            @endforeach

            @if ($autoFields !== [])
                <div class="rounded border border-emerald-100 bg-emerald-50/60 p-2 text-xs text-emerald-900">
                    <p class="mb-1 font-semibold">Data otomatis dari paket pekerjaan</p>
                    <dl class="grid gap-1 sm:grid-cols-2">
                        @foreach ($autoFields as $field)
                            <div>
                                <dt class="text-emerald-700">{{ $field['label'] }}</dt>
                                <dd class="font-medium">
                                    @if ($field['type'] === 'number' && isset($savedInput[$field['name']]))
                                        Rp {{ number_format((int) $savedInput[$field['name']], 0, ',', '.') }}
                                    @else
                                        {{ $savedInput[$field['name']] ?? '—' }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            @if ($userFields !== [])
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-gray-700">Input manual</p>
                    @foreach ($userFields as $field)
                        <div>
                            <label class="mb-0.5 block text-xs font-medium text-gray-600">
                                {{ $field['label'] }}@if ($field['required'] ?? false) * @endif
                            </label>
                            @if ($field['type'] === 'textarea')
                                <textarea name="fields[{{ $field['name'] }}]" rows="2"
                                          @if ($field['required'] ?? false) required @endif
                                          class="w-full rounded border border-gray-300 px-2 py-1 text-xs">{{ old('fields.'.$field['name'], $savedInput[$field['name']] ?? '') }}</textarea>
                            @else
                                <input type="{{ $field['type'] === 'number' ? 'number' : ($field['type'] === 'date' ? 'date' : 'text') }}"
                                       name="fields[{{ $field['name'] }}]"
                                       value="{{ old('fields.'.$field['name'], $savedInput[$field['name']] ?? '') }}"
                                       @if ($field['required'] ?? false) required @endif
                                       @if ($field['type'] === 'number') min="0" @endif
                                       class="w-full rounded border border-gray-300 px-2 py-1 text-xs">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if (\App\Support\DpaDocumentFields::usesItemTable($def['kode']))
                <p class="text-xs text-gray-500">
                    Item rinci diambil dari daftar {{ $def['kode'] === 'hps' ? 'HPS' : 'SPK' }} di bawah. Pastikan item sudah diisi.
                </p>
            @endif

            <div class="flex flex-wrap gap-2 pt-1">
                <button type="submit" class="rounded bg-green-600 px-3 py-1 text-xs font-medium text-white hover:bg-green-700">
                    Generate PDF
                </button>
                @if ($doc?->file_path && $doc->input_data)
                    <a href="{{ route('admin.dpa.paket-pekerjaans.dokumens.preview', [$paketPekerjaan, 'kode_dokumen' => $def['kode']]) }}"
                       target="_blank"
                       class="rounded border border-gray-300 px-3 py-1 text-xs text-gray-700 hover:bg-gray-100">
                        Preview
                    </a>
                @endif
            </div>
        </form>
    @endif

    <details class="mt-2">
        <summary class="cursor-pointer text-xs text-gray-500 hover:text-gray-700">Unggah file manual (opsional)</summary>
        <form action="{{ route('admin.dpa.paket-pekerjaans.dokumens.store', $paketPekerjaan) }}" method="POST" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-end gap-2">
            @csrf
            <input type="hidden" name="tahap" value="{{ $activeTahap }}">
            <input type="hidden" name="kode_dokumen" value="{{ $def['kode'] }}">
            <input type="file" name="file" required class="text-xs">
            <button type="submit" class="rounded bg-gray-700 px-2 py-1 text-xs text-white hover:bg-gray-800">Unggah</button>
        </form>
    </details>
</div>
