@extends('layouts.admin')

@section('title', 'Edit Taman')
@section('header', 'Edit Taman')

@section('content')
    <div class="mx-auto max-w-5xl">
        <x-admin.rth-page-toolbar
            :back-url="route('admin.tamans.show', $taman)"
            back-label="← Kembali ke profil taman" />

        <form action="{{ route('admin.tamans.update', $taman) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.tamans._form', ['taman' => $taman])
        </form>
    </div>

    @foreach ($taman->images as $image)
        <form id="delete-image-{{ $image->id }}"
              action="{{ route('admin.tamans.images.destroy', [$taman, $image]) }}"
              method="POST"
              class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @include('admin.tamans.partials.location-map-assets')
@endsection

@push('scripts')
    <script>
        (function () {
            const list = document.getElementById('taman-gallery-order-list');
            if (! list || ! list.dataset.reorderUrl) return;

            let dragEl = null;

            function refreshGalleryOrderUi() {
                list.querySelectorAll('.taman-gallery-order-item').forEach(function (item, index) {
                    const badge = item.querySelector('.taman-gallery-order-badge');
                    const caption = item.querySelector('.taman-gallery-order-caption');
                    if (badge) badge.textContent = '#' + (index + 1);
                    if (caption) caption.textContent = index === 0 ? 'Sampul slider' : 'Galeri';
                });
            }

            list.querySelectorAll('.taman-gallery-order-item').forEach(function (item) {
                item.draggable = true;
                item.addEventListener('dragstart', function (event) {
                    dragEl = item;
                    item.classList.add('opacity-60', 'ring-2', 'ring-emerald-400');
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                    }
                });
                item.addEventListener('dragend', function () {
                    item.classList.remove('opacity-60', 'ring-2', 'ring-emerald-400');
                    dragEl = null;
                    refreshGalleryOrderUi();
                });
                item.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    if (! dragEl || dragEl === item) return;
                    const rect = item.getBoundingClientRect();
                    const midX = rect.left + rect.width / 2;
                    const midY = rect.top + rect.height / 2;
                    const preferHorizontal = Math.abs(event.clientX - midX) > Math.abs(event.clientY - midY);
                    const after = preferHorizontal ? event.clientX > midX : event.clientY > midY;
                    list.insertBefore(dragEl, after ? item.nextSibling : item);
                });
            });

            const saveBtn = document.getElementById('taman-gallery-save-order');
            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = list.dataset.reorderUrl;

                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = list.dataset.csrf;
                    form.appendChild(token);

                    list.querySelectorAll('.taman-gallery-order-item').forEach(function (item) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'order[]';
                        input.value = item.dataset.imageId;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                });
            }
        })();
    </script>
@endpush
