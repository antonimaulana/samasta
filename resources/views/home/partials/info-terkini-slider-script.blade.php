@once
    @push('scripts')
        <script>
            (function () {
                function bootInfoSlider() {
                    var section = document.getElementById('info-terkini-slider');
                    var track = document.getElementById('info-slider-track');
                    if (!section || !track || section.dataset.sliderReady === '1') {
                        return;
                    }

                    var viewport = track.closest('.info-slide-viewport') || track.parentElement;
                    if (!viewport) {
                        return;
                    }

                    var slides = track.querySelectorAll('.info-slide');
                    if (!slides.length) {
                        return;
                    }

                    section.dataset.sliderReady = '1';

                    var dotsRoot = section.querySelector('#info-slider-dots');
                    var dots = dotsRoot ? dotsRoot.querySelectorAll('.info-slider-dot') : [];
                    var prevBtns = [
                        document.getElementById('info-slider-prev'),
                        document.getElementById('info-slider-prev-mobile'),
                    ];
                    var nextBtns = [
                        document.getElementById('info-slider-next'),
                        document.getElementById('info-slider-next-mobile'),
                    ];

                    var current = 0;
                    var autoplayTimer = null;
                    var isPaused = false;
                    var INTERVAL = 6000;
                    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    function slideWidth() {
                        return viewport.getBoundingClientRect().width || viewport.offsetWidth || 0;
                    }

                    function applySlideWidths() {
                        var w = slideWidth();
                        if (w <= 0) {
                            return false;
                        }
                        for (var i = 0; i < slides.length; i++) {
                            slides[i].style.flex = '0 0 ' + w + 'px';
                            slides[i].style.width = w + 'px';
                            slides[i].style.maxWidth = w + 'px';
                        }
                        track.style.width = w * slides.length + 'px';
                        return true;
                    }

                    function goTo(index) {
                        var w = slideWidth();
                        if (w <= 0) {
                            return;
                        }
                        current = (index + slides.length) % slides.length;
                        track.style.transform = 'translate3d(-' + (w * current) + 'px, 0, 0)';

                        for (var d = 0; d < dots.length; d++) {
                            if (d === current) {
                                dots[d].classList.remove('w-2.5', 'bg-green-200');
                                dots[d].classList.add('w-8', 'bg-green-500');
                            } else {
                                dots[d].classList.remove('w-8', 'bg-green-500');
                                dots[d].classList.add('w-2.5', 'bg-green-200');
                            }
                        }
                    }

                    function next() {
                        goTo(current + 1);
                    }

                    function prev() {
                        goTo(current - 1);
                    }

                    function stopAutoplay() {
                        if (autoplayTimer !== null) {
                            clearTimeout(autoplayTimer);
                            autoplayTimer = null;
                        }
                    }

                    function scheduleAutoplay() {
                        stopAutoplay();
                        if (slides.length <= 1 || reducedMotion || isPaused || document.hidden) {
                            return;
                        }
                        autoplayTimer = setTimeout(function () {
                            next();
                            scheduleAutoplay();
                        }, INTERVAL);
                    }

                    function startAutoplay() {
                        isPaused = false;
                        scheduleAutoplay();
                    }

                    function pauseAutoplay() {
                        isPaused = true;
                        stopAutoplay();
                    }

                    function bindClick(el, fn) {
                        if (el) {
                            el.addEventListener('click', fn);
                        }
                    }

                    prevBtns.forEach(function (btn) {
                        bindClick(btn, function () {
                            prev();
                            startAutoplay();
                        });
                    });

                    nextBtns.forEach(function (btn) {
                        bindClick(btn, function () {
                            next();
                            startAutoplay();
                        });
                    });

                    for (var i = 0; i < dots.length; i++) {
                        (function (dot) {
                            bindClick(dot, function () {
                                goTo(parseInt(dot.getAttribute('data-index'), 10));
                                startAutoplay();
                            });
                        })(dots[i]);
                    }

                    viewport.addEventListener('mouseenter', pauseAutoplay);
                    viewport.addEventListener('mouseleave', startAutoplay);

                    document.addEventListener('visibilitychange', function () {
                        if (document.hidden) {
                            stopAutoplay();
                        } else {
                            scheduleAutoplay();
                        }
                    });

                    window.addEventListener('resize', function () {
                        applySlideWidths();
                        goTo(current);
                    });

                    window.addEventListener('pageshow', function (event) {
                        if (event.persisted) {
                            applySlideWidths();
                            goTo(current);
                            startAutoplay();
                        }
                    });

                    if (typeof ResizeObserver !== 'undefined') {
                        var ro = new ResizeObserver(function () {
                            applySlideWidths();
                            goTo(current);
                        });
                        ro.observe(viewport);
                    }

                    function initLayout(retry) {
                        if (!applySlideWidths() && retry < 20) {
                            window.setTimeout(function () {
                                initLayout(retry + 1);
                            }, 50);
                            return;
                        }
                        goTo(0);
                        startAutoplay();
                    }

                    initLayout(0);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bootInfoSlider);
                } else {
                    bootInfoSlider();
                }
            })();
        </script>
    @endpush
@endonce
