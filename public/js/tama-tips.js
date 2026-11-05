/**
 * Pesan Tama di beranda — file statis (tidak bergantung Vite build di server).
 * Logika diselaraskan dengan resources/js/tama-tips.js
 */
(function () {
    const STORAGE_KEY = 'simtaman_tama_tips_v1';

    function readStorage() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : { dismissed: [], skipAll: false };
        } catch {
            return { dismissed: [], skipAll: false };
        }
    }

    function writeStorage(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch {
            /* ignore */
        }
    }

    function bootTamaTips() {
        const root = document.getElementById('tama-tip-root');
        if (!root) {
            return;
        }

        let tips;
        try {
            tips = JSON.parse(root.dataset.tips || '[]');
        } catch {
            return;
        }

        if (!Array.isArray(tips) || tips.length === 0) {
            return;
        }

        const storage = readStorage();
        if (storage.skipAll) {
            return;
        }

        const tipByKey = new Map(tips.map((t) => [t.section_key, t]));
        if (tips.map((t) => t.section_key).filter((key) => !storage.dismissed.includes(key)).length === 0) {
            return;
        }

        const titleEl = document.getElementById('tama-tip-title');
        const bodyEl = document.getElementById('tama-tip-body');
        const imageEl = document.getElementById('tama-tip-image');
        const ctaEl = document.getElementById('tama-tip-cta');

        let currentKey = null;
        let isOpen = false;
        const pendingKeys = new Set();
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function showTip(sectionKey) {
            const tip = tipByKey.get(sectionKey);
            if (!tip || isOpen) {
                pendingKeys.add(sectionKey);
                return;
            }

            currentKey = sectionKey;
            isOpen = true;

            titleEl.textContent = tip.title || '';
            bodyEl.textContent = tip.body || '';
            imageEl.src = tip.image_url || '';
            imageEl.alt = 'Tama — ' + (tip.title || 'petunjuk beranda');

            if (tip.cta_label && tip.cta_url) {
                ctaEl.textContent = tip.cta_label;
                ctaEl.href = tip.cta_url;
                ctaEl.classList.remove('hidden');
            } else {
                ctaEl.classList.add('hidden');
            }

            root.classList.remove('hidden');
            root.setAttribute('aria-hidden', 'false');
            root.classList.remove('is-visible');
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    root.classList.add('is-visible');
                });
            });
        }

        function dismissCurrent(markDismissed) {
            if (markDismissed === undefined) {
                markDismissed = true;
            }
            if (!isOpen) {
                return;
            }

            root.classList.remove('is-visible');
            root.setAttribute('aria-hidden', 'true');

            const key = currentKey;
            const done = function () {
                root.classList.add('hidden');
                isOpen = false;
                currentKey = null;

                if (markDismissed && key) {
                    const state = readStorage();
                    if (state.dismissed.indexOf(key) === -1) {
                        state.dismissed.push(key);
                        writeStorage(state);
                    }
                }

                const next = Array.from(pendingKeys).find(function (k) {
                    return readStorage().dismissed.indexOf(k) === -1;
                });
                pendingKeys.clear();
                if (next) {
                    setTimeout(function () {
                        showTip(next);
                    }, prefersReducedMotion ? 0 : 400);
                }
            };

            if (prefersReducedMotion) {
                done();
            } else {
                setTimeout(done, 320);
            }
        }

        root.querySelectorAll('[data-tama-dismiss]').forEach(function (el) {
            el.addEventListener('click', function () {
                dismissCurrent(true);
            });
        });

        var skipBtn = root.querySelector('[data-tama-skip-all]');
        if (skipBtn) {
            skipBtn.addEventListener('click', function () {
                writeStorage({ dismissed: tips.map(function (t) { return t.section_key; }), skipAll: true });
                dismissCurrent(false);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && isOpen) {
                dismissCurrent(true);
            }
        });

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    var sectionKey = entry.target.dataset.tamaSection;
                    if (!sectionKey || readStorage().dismissed.indexOf(sectionKey) !== -1) {
                        return;
                    }

                    if (sectionKey !== 'hero' && entry.intersectionRatio < 0.35) {
                        return;
                    }

                    if (sectionKey === 'hero') {
                        setTimeout(function () {
                            showTip(sectionKey);
                        }, prefersReducedMotion ? 0 : 1200);
                    } else {
                        showTip(sectionKey);
                    }

                    observer.unobserve(entry.target);
                });
            },
            { threshold: [0, 0.15, 0.35, 0.5], rootMargin: '0px 0px -5% 0px' },
        );

        tips.forEach(function (tip) {
            var el = document.getElementById(tip.dom_id);
            if (!el) {
                return;
            }
            el.dataset.tamaSection = tip.section_key;
            observer.observe(el);
        });
    }

    function scheduleBoot() {
        if (typeof window.onPageReady === 'function') {
            window.onPageReady(bootTamaTips);
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootTamaTips);
        } else {
            bootTamaTips();
        }
    }

    scheduleBoot();
})();
