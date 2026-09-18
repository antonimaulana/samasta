<script>
    window.initSidebarAccordion = window.initSidebarAccordion || function (root) {
        var scope = root || document;
        scope.querySelectorAll('.sidebar-menu-group').forEach(function (group) {
            var trigger = group.querySelector('.sidebar-menu-trigger');
            if (!trigger || trigger.dataset.accordionBound === '1') {
                return;
            }
            trigger.dataset.accordionBound = '1';
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var willOpen = !group.classList.contains('is-open');
                var nav = group.closest('nav');
                if (nav) {
                    nav.querySelectorAll('.sidebar-menu-group.is-open').forEach(function (other) {
                        if (other !== group) {
                            other.classList.remove('is-open');
                        }
                    });
                }
                group.classList.toggle('is-open', willOpen);
            });
        });
    };
</script>
