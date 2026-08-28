@props(['href', 'label' => 'Kembali'])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'mb-4 inline-flex items-center gap-2 rounded-xl border border-green-200 bg-white/80 px-3 py-2 text-sm font-semibold text-green-800 shadow-sm transition hover:border-green-300 hover:bg-green-50']) }}>
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    {{ $label }}
</a>
