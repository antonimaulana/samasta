@once
    @push('scripts')
        <script>
            (function () {
                const section = document.getElementById('faq-beranda');
                if (!section) return;

                function closeAll(exceptPanel) {
                    section.querySelectorAll('.faq-item__trigger').forEach(function (btn) {
                        const panelId = btn.getAttribute('aria-controls');
                        const panel = panelId ? document.getElementById(panelId) : null;
                        if (panel === exceptPanel) return;
                        btn.setAttribute('aria-expanded', 'false');
                        btn.closest('.faq-item')?.classList.remove('is-open');
                        if (panel) panel.hidden = true;
                    });
                }

                section.querySelectorAll('.faq-item__trigger').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const panelId = btn.getAttribute('aria-controls');
                        const panel = panelId ? document.getElementById(panelId) : null;
                        const isOpen = btn.getAttribute('aria-expanded') === 'true';
                        if (isOpen) {
                            btn.setAttribute('aria-expanded', 'false');
                            btn.closest('.faq-item')?.classList.remove('is-open');
                            if (panel) panel.hidden = true;
                            return;
                        }
                        closeAll(panel);
                        btn.setAttribute('aria-expanded', 'true');
                        btn.closest('.faq-item')?.classList.add('is-open');
                        if (panel) panel.hidden = false;
                    });
                });
            })();
        </script>
    @endpush
@endonce
