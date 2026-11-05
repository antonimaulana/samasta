@if (! empty($tamaTipsPayload))
    <div id="tama-tip-root"
         class="tama-tip-root hidden"
         role="dialog"
         aria-modal="false"
         aria-labelledby="tama-tip-title"
         aria-describedby="tama-tip-body"
         aria-hidden="true"
         data-tips='@json($tamaTipsPayload)'>
        <div class="tama-tip-widget">
            <div class="tama-tip-widget__bubble">
                <button type="button" class="tama-tip-widget__close" data-tama-dismiss aria-label="Tutup pesan Tama">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <p class="tama-tip-widget__kicker">Tama · petugas pemeliharaan taman</p>
                <h2 id="tama-tip-title" class="tama-tip-widget__title"></h2>
                <p id="tama-tip-body" class="tama-tip-widget__body"></p>
                <div class="tama-tip-widget__actions">
                    <button type="button" class="tama-tip-widget__primary" data-tama-dismiss>Oke, Tama!</button>
                    <a id="tama-tip-cta" href="#" class="tama-tip-widget__cta hidden"></a>
                </div>
                <button type="button" class="tama-tip-widget__skip" data-tama-skip-all>Lewati semua tips</button>
            </div>
            <div class="tama-tip-widget__stage" aria-hidden="true">
                <div class="tama-tip-widget__shadow"></div>
                <img id="tama-tip-image" src="" alt="" width="200" height="240" decoding="async" class="tama-tip-widget__img">
            </div>
        </div>
    </div>

    @push('scripts')
        @php
            $tamaTipsScript = public_path('js/tama-tips.js');
            $tamaTipsVersion = is_readable($tamaTipsScript) ? (string) filemtime($tamaTipsScript) : '1';
        @endphp
        <script src="{{ asset('js/tama-tips.js') }}?v={{ $tamaTipsVersion }}" defer></script>
    @endpush
@endif
