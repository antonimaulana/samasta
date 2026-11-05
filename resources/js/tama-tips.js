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
        /* ignore quota */
    }
}

export function bootTamaTips() {
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
    const queue = tips
        .map((t) => t.section_key)
        .filter((key) => !storage.dismissed.includes(key));

    if (queue.length === 0) {
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
        imageEl.alt = `Tama — ${tip.title || 'petunjuk beranda'}`;

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
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                root.classList.add('is-visible');
            });
        });
    }

    function dismissCurrent(markDismissed = true) {
        if (!isOpen) {
            return;
        }

        root.classList.remove('is-visible');
        root.setAttribute('aria-hidden', 'true');

        const key = currentKey;
        const done = () => {
            root.classList.add('hidden');
            isOpen = false;
            currentKey = null;

            if (markDismissed && key) {
                const state = readStorage();
                if (!state.dismissed.includes(key)) {
                    state.dismissed.push(key);
                    writeStorage(state);
                }
            }

            const next = [...pendingKeys].find((k) => !readStorage().dismissed.includes(k));
            pendingKeys.clear();
            if (next) {
                setTimeout(() => showTip(next), prefersReducedMotion ? 0 : 400);
            }
        };

        if (prefersReducedMotion) {
            done();
        } else {
            setTimeout(done, 320);
        }
    }

    root.querySelectorAll('[data-tama-dismiss]').forEach((el) => {
        el.addEventListener('click', () => dismissCurrent(true));
    });

    const skipBtn = root.querySelector('[data-tama-skip-all]');
    skipBtn?.addEventListener('click', () => {
        writeStorage({ dismissed: tips.map((t) => t.section_key), skipAll: true });
        dismissCurrent(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            dismissCurrent(true);
        }
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const sectionKey = entry.target.dataset.tamaSection;
                if (!sectionKey || readStorage().dismissed.includes(sectionKey)) {
                    return;
                }

                if (sectionKey !== 'hero' && entry.intersectionRatio < 0.35) {
                    return;
                }

                if (sectionKey === 'hero') {
                    setTimeout(() => showTip(sectionKey), prefersReducedMotion ? 0 : 1200);
                } else {
                    showTip(sectionKey);
                }

                observer.unobserve(entry.target);
            });
        },
        { threshold: [0, 0.15, 0.35, 0.5], rootMargin: '0px 0px -5% 0px' },
    );

    tips.forEach((tip) => {
        const domId = tip.dom_id;
        const el = document.getElementById(domId);
        if (!el) {
            return;
        }
        el.dataset.tamaSection = tip.section_key;
        observer.observe(el);
    });
}

