@extends('layouts.admin')

@section('title', 'Ensiklopedia')
@section('header', 'Konten Ensiklopedia')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="max-w-2xl text-sm text-gray-600">
            Kelola kategori, artikel, serta teks hero dan fitur interaktif portal ensiklopedia dalam satu halaman.
        </p>
        <a href="{{ route('ensiklopedia.index') }}" target="_blank" rel="noopener"
           class="text-sm font-semibold text-green-700 hover:underline">
            Pratinjau portal ↗
        </a>
    </div>

    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-2" role="tablist">
        @foreach (['kategori' => 'Kategori', 'artikel' => 'Artikel', 'portal' => 'Portal & interaktif'] as $key => $label)
            <a href="{{ route('admin.ensiklopedia-pengaturan.index', array_merge(request()->except('tab'), ['tab' => $key])) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold {{ $tab === $key ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($tab === 'kategori')
        @include('admin.ensiklopedia.partials.kategoris-panel')
    @elseif ($tab === 'artikel')
        @include('admin.ensiklopedia.partials.artikels-panel')
    @else
        @include('admin.ensiklopedia.partials.portal-panel')
    @endif
@endsection

@push('scripts')
    @if ($tab === 'portal')
        <script>
            (function () {
                const tabs = document.querySelectorAll('.portal-tab');
                const panels = document.querySelectorAll('.portal-panel');
                function showPortalTab(name) {
                    tabs.forEach(function (t) {
                        const active = t.dataset.portalTab === name;
                        t.classList.toggle('bg-green-600', active);
                        t.classList.toggle('text-white', active);
                        t.classList.toggle('text-gray-600', !active);
                    });
                    panels.forEach(function (p) {
                        p.classList.toggle('hidden', p.dataset.portalPanel !== name);
                    });
                }
                tabs.forEach(function (t) {
                    t.addEventListener('click', function () { showPortalTab(t.dataset.portalTab); });
                });
                showPortalTab('hero');

                document.querySelectorAll('.add-tip').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const field = btn.dataset.target;
                        const container = document.getElementById(field + '-rows');
                        const index = container.querySelectorAll('.tip-row').length;
                        let html = document.getElementById('tip-row-template').innerHTML;
                        html = html.replaceAll('__FIELD__', field).replaceAll('__INDEX__', String(index));
                        container.insertAdjacentHTML('beforeend', html);
                    });
                });

                document.getElementById('add-kuis')?.addEventListener('click', function () {
                    const container = document.getElementById('kuis-rows');
                    const index = container.querySelectorAll('.kuis-row').length;
                    let html = document.getElementById('kuis-row-template').innerHTML;
                    html = html.replaceAll('__INDEX__', String(index));
                    container.insertAdjacentHTML('beforeend', html);
                });

                document.getElementById('ensiklopedia-portal-form')?.addEventListener('click', function (e) {
                    if (e.target.classList.contains('remove-tip-row')) {
                        e.target.closest('.tip-row')?.remove();
                    }
                    if (e.target.classList.contains('remove-kuis-row')) {
                        e.target.closest('.kuis-row')?.remove();
                    }
                });
            })();
        </script>
    @endif
@endpush
