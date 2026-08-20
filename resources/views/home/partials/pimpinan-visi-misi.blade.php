<article @class([
    'group flex h-full flex-col overflow-hidden rounded-3xl border border-violet-100 bg-white p-6 shadow-lg shadow-violet-100/40 ring-1 ring-violet-50 transition hover:-translate-y-1 hover:shadow-xl lg:p-6',
    $cardClass ?? '',
])>
    <div class="flex items-start gap-2 text-violet-700">
        <h3 class="text-base font-bold leading-snug text-violet-800 sm:text-lg">Visi dan Misi Kota Batam</h3>
    </div>
    <div class="mt-4 flex-1 space-y-4 text-sm leading-relaxed text-gray-600 lg:space-y-3">
        <p>
            <span class="font-bold text-gray-800">Visi:</span>
            {{ $visiMisi['visi'] }}
        </p>
        <p>
            <span class="font-bold text-gray-800">Misi:</span>
            {{ $visiMisi['misi'] }}
        </p>
    </div>
</article>
