<script>
(function () {
    function initMobileDrawer(config) {
        var toggle = document.getElementById(config.toggleId);
        var closeBtn = document.getElementById(config.closeId);
        var drawer = document.getElementById(config.drawerId);
        var backdrop = document.getElementById(config.backdropId);
        var iconOpen = config.iconOpenId ? document.getElementById(config.iconOpenId) : null;
        var iconClose = config.iconCloseId ? document.getElementById(config.iconCloseId) : null;
        var bodyClass = config.bodyClass || 'mobile-nav-open';
        var mobileQuery = window.matchMedia('(max-width: 767px)');

        if (!toggle || !drawer || !backdrop) {
            return;
        }

        function isMobile() {
            return mobileQuery.matches;
        }

        function raiseLayers() {
            document.body.appendChild(backdrop);
            document.body.appendChild(drawer);
        }

        function applyBackdrop(open) {
            if (!open) {
                backdrop.style.display = 'none';
                return;
            }

            backdrop.style.display = 'block';
            backdrop.style.position = 'fixed';
            backdrop.style.top = '0';
            backdrop.style.left = '0';
            backdrop.style.right = '0';
            backdrop.style.bottom = '0';
            backdrop.style.zIndex = '99999';
            backdrop.style.background = 'rgba(17, 24, 39, 0.45)';
            backdrop.style.opacity = '1';
            backdrop.style.pointerEvents = 'auto';
        }

        function applyDrawer(open) {
            if (!open) {
                drawer.style.display = 'none';
                return;
            }

            drawer.style.display = 'block';
            drawer.style.position = 'fixed';
            drawer.style.top = '0';
            drawer.style.bottom = '0';
            drawer.style.width = '85vw';
            drawer.style.maxWidth = config.width + 'px';
            drawer.style.minWidth = Math.min(config.width, 260) + 'px';
            drawer.style.zIndex = '100000';
            drawer.style.background = config.background;
            drawer.style.color = config.color || '#111827';
            drawer.style.overflowX = 'hidden';
            drawer.style.overflowY = 'auto';
            drawer.style.boxShadow = config.shadow;
            drawer.style.transform = 'none';
            drawer.style.webkitTransform = 'none';
            drawer.style.visibility = 'visible';
            drawer.style.opacity = '1';
            drawer.style.pointerEvents = 'auto';

            if (config.side === 'left') {
                drawer.style.left = '0';
                drawer.style.right = 'auto';
            } else {
                drawer.style.right = '0';
                drawer.style.left = 'auto';
            }
        }

        function setOpen(open) {
            if (!isMobile()) {
                open = false;
            }

            if (open) {
                raiseLayers();
            }

            drawer.classList.toggle('is-open', open);
            backdrop.classList.toggle('is-open', open);
            document.body.classList.toggle(bodyClass, open);
            applyBackdrop(open);
            applyDrawer(open);

            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
            backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');

            if (iconOpen && iconClose) {
                iconOpen.style.display = open ? 'none' : 'block';
                iconClose.style.display = open ? 'block' : 'none';
            }
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setOpen(!drawer.classList.contains('is-open'));
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                setOpen(false);
            });
        }

        backdrop.addEventListener('click', function () {
            setOpen(false);
        });

        drawer.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                setOpen(false);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });

        mobileQuery.addEventListener('change', function () {
            if (!isMobile()) {
                setOpen(false);
            }
        });

        setOpen(false);
    }

    window.initMobileDrawer = initMobileDrawer;
})();
</script>
