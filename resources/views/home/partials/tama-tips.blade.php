@if (! empty($tamaTipsPayload))
    <div id="tama-tip-root"
         class="tama-tip-root hidden"
         role="dialog"
         aria-modal="false"
         aria-labelledby="tama-tip-title"
         aria-hidden="true"
         data-tips='@json($tamaTipsPayload)'>
        <div class="tama-tip-root__backdrop" data-tama-dismiss aria-hidden="true"></div>
        <div class="tama-tip-card">
            <button type="button" class="tama-tip-card__close" data-tama-dismiss aria-label="Tutup pesan Tama">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="tama-tip-card__inner">
                <div class="tama-tip-card__figure">
                    <img id="tama-tip-image" src="" alt="" width="160" height="200" decoding="async" class="tama-tip-card__img">
                </div>
                <div class="tama-tip-card__bubble">
                    <p class="tama-tip-card__kicker">Tama · Petugas pemeliharaan taman</p>
                    <h2 id="tama-tip-title" class="tama-tip-card__title"></h2>
                    <p id="tama-tip-body" class="tama-tip-card__body"></p>
                    <div class="tama-tip-card__actions">
                        <a id="tama-tip-cta" href="#" class="tama-tip-card__cta hidden"></a>
                        <button type="button" class="tama-tip-card__primary" data-tama-dismiss>Mengerti</button>
                        <button type="button" class="tama-tip-card__skip" data-tama-skip-all>Lewati semua tips</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
