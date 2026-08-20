function initInfoSlider() {
    const track = document.getElementById('info-slider-track');
    const slides = document.querySelectorAll('.info-slide');
    const dots = document.querySelectorAll('.info-slider-dot');
    const prevBtns = [document.getElementById('info-slider-prev'), document.getElementById('info-slider-prev-mobile')];
    const nextBtns = [document.getElementById('info-slider-next'), document.getElementById('info-slider-next-mobile')];

    if (!track || slides.length === 0) {
        return;
    }

    let current = 0;
    let autoplayTimer = null;
    const INTERVAL = 6000;

    function goTo(index) {
        current = (index + slides.length) % slides.length;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';

        dots.forEach(function (dot, i) {
            if (i === current) {
                dot.classList.remove('w-2.5', 'bg-green-200');
                dot.classList.add('w-8', 'bg-green-500');
            } else {
                dot.classList.remove('w-8', 'bg-green-500');
                dot.classList.add('w-2.5', 'bg-green-200');
            }
        });
    }

    function next() {
        goTo(current + 1);
    }

    function prev() {
        goTo(current - 1);
    }

    function startAutoplay() {
        clearInterval(autoplayTimer);
        autoplayTimer = setInterval(next, INTERVAL);
    }

    prevBtns.forEach(function (btn) {
        btn?.addEventListener('click', function () {
            prev();
            startAutoplay();
        });
    });

    nextBtns.forEach(function (btn) {
        btn?.addEventListener('click', function () {
            next();
            startAutoplay();
        });
    });

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.dataset.index, 10));
            startAutoplay();
        });
    });

    const slider = document.getElementById('info-terkini-slider');
    slider?.addEventListener('mouseenter', function () {
        clearInterval(autoplayTimer);
    });
    slider?.addEventListener('mouseleave', startAutoplay);

    goTo(0);
    startAutoplay();
}

function initFaktaRotator() {
    const faktaCard = document.getElementById('fakta-rotator');

    if (!faktaCard) {
        return;
    }

    let faktaList = [];

    try {
        faktaList = JSON.parse(faktaCard.dataset.fakta || '[]');
    } catch {
        faktaList = [];
    }

    const faktaText = document.getElementById('fakta-rotator-text');
    const faktaIndex = document.getElementById('fakta-rotator-index');
    const faktaDots = document.getElementById('fakta-rotator-dots');
    const faktaNext = document.getElementById('fakta-rotator-next');

    if (faktaList.length === 0 || !faktaText) {
        return;
    }

    let faktaCurrent = Math.floor(Math.random() * faktaList.length);
    let faktaTimer = null;
    const FAKTA_INTERVAL = 5000;

    faktaList.forEach(function (_, i) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'h-1.5 rounded-full bg-white/30 transition-all';
        dot.style.width = i === faktaCurrent ? '1.25rem' : '0.375rem';
        dot.setAttribute('aria-label', 'Fakta ' + (i + 1));
        dot.addEventListener('click', function () {
            showFakta(i);
            startFaktaAutoplay();
        });
        faktaDots?.appendChild(dot);
    });

    function updateFaktaDots() {
        faktaDots?.querySelectorAll('button').forEach(function (dot, i) {
            dot.style.width = i === faktaCurrent ? '1.25rem' : '0.375rem';
            dot.classList.toggle('bg-white/80', i === faktaCurrent);
            dot.classList.toggle('bg-white/30', i !== faktaCurrent);
        });
    }

    function showFakta(index) {
        if (index === faktaCurrent && faktaText.textContent === faktaList[index]) {
            return;
        }

        faktaText.classList.add('is-leaving');

        setTimeout(function () {
            faktaCurrent = (index + faktaList.length) % faktaList.length;
            faktaText.textContent = faktaList[faktaCurrent];

            if (faktaIndex) {
                faktaIndex.textContent = String(faktaCurrent + 1);
            }

            faktaText.classList.remove('is-leaving');
            faktaText.classList.add('is-entering');
            updateFaktaDots();

            requestAnimationFrame(function () {
                faktaText.classList.remove('is-entering');
            });
        }, 200);
    }

    function nextFakta() {
        showFakta(faktaCurrent + 1);
    }

    function startFaktaAutoplay() {
        clearInterval(faktaTimer);
        faktaTimer = setInterval(nextFakta, FAKTA_INTERVAL);
    }

    faktaNext?.addEventListener('click', function () {
        nextFakta();
        startFaktaAutoplay();
    });

    faktaCard.addEventListener('mouseenter', function () {
        clearInterval(faktaTimer);
    });

    faktaCard.addEventListener('mouseleave', startFaktaAutoplay);

    showFakta(faktaCurrent);
    startFaktaAutoplay();
}

window.onPageReady(function () {
    initInfoSlider();
    initFaktaRotator();
});
