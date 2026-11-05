function bootHomePage() {
    /* Slider info terkini: inline script di partial. FAQ: faq-beranda-script. Tama: public/js/tama-tips.js */
}

if (typeof window.onPageReady === 'function') {
    window.onPageReady(bootHomePage);
} else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootHomePage);
} else {
    bootHomePage();
}
