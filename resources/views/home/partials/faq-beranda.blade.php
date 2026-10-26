@php
    $faqSetting = $faqSetting ?? null;
    $faqs = $faqs ?? collect();
    $badge = $faqSetting?->section_badge ?? 'FAQ';
    $title = $faqSetting?->section_title ?? 'Pertanyaan yang Sering Diajukan';
@endphp

<section id="faq-beranda" class="faq-beranda">
    <div class="faq-beranda__header text-center">
        @if ($badge)
            <span class="faq-beranda__badge">{{ $badge }}</span>
        @endif
        <h2 class="faq-beranda__title">{{ $title }}</h2>
    </div>

    @if ($faqs->isNotEmpty())
        <div class="faq-beranda__list" id="faq-accordion" role="list">
            @foreach ($faqs as $index => $faq)
                <article @class(['faq-item', 'is-open' => $index === 0]) role="listitem">
                    <h3 class="faq-item__heading">
                        <button type="button"
                                class="faq-item__trigger"
                                id="faq-trigger-{{ $faq->id }}"
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-controls="faq-panel-{{ $faq->id }}">
                            <span class="faq-item__q">{{ $faq->question }}</span>
                            <span class="faq-item__icon" aria-hidden="true"></span>
                        </button>
                    </h3>
                    <div id="faq-panel-{{ $faq->id }}"
                         class="faq-item__panel"
                         role="region"
                         aria-labelledby="faq-trigger-{{ $faq->id }}"
                         @if ($index !== 0) hidden @endif>
                        <div class="faq-item__answer">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <p class="faq-beranda__empty text-center">Belum ada FAQ untuk ditampilkan.</p>
    @endif
</section>

@include('home.partials.faq-beranda-script')
