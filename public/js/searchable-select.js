function initSearchableSelect(root) {
    if (!root || root.dataset.searchableReady === 'true') {
        return;
    }

    const input = root.querySelector('[data-searchable-input]');
    const hidden = root.querySelector('[data-searchable-value]');
    const list = root.querySelector('[data-searchable-list]');

    if (!input || !hidden || !list) {
        return;
    }

    const items = Array.from(list.querySelectorAll('li[data-value]'));
    const emptyText = list.dataset.emptyText || 'Tidak ada hasil.';
    let activeIndex = -1;

    root.dataset.searchableReady = 'true';

    function openList() {
        list.classList.remove('hidden');
        filterList(input.value);
    }

    function closeList() {
        list.classList.add('hidden');
        activeIndex = -1;
        clearActiveItem();
    }

    function clearActiveItem() {
        items.forEach(function (item) {
            item.classList.remove('bg-green-100');
        });
    }

    function filterList(query) {
        const term = query.trim().toLowerCase();
        let visibleCount = 0;

        items.forEach(function (item) {
            const matchesSearch = term === '' || item.dataset.search.includes(term);
            const matchesWilayah = !item.classList.contains('wilayah-filtered-out');
            const matches = matchesSearch && matchesWilayah;
            item.classList.toggle('hidden', !matches);

            if (matches) {
                visibleCount++;
            }
        });

        let emptyState = list.querySelector('[data-searchable-empty]');

        if (visibleCount === 0) {
            if (!emptyState) {
                emptyState = document.createElement('li');
                emptyState.dataset.searchableEmpty = 'true';
                emptyState.className = 'px-3 py-2 text-sm italic text-gray-500';
                emptyState.textContent = emptyText;
                list.appendChild(emptyState);
            }

            emptyState.classList.remove('hidden');
        } else if (emptyState) {
            emptyState.classList.add('hidden');
        }
    }

    function dispatchChange(detail) {
        root.dispatchEvent(new CustomEvent('searchable-select:change', {
            bubbles: true,
            detail: detail,
        }));
    }

    function selectItem(item) {
        hidden.value = item.dataset.value;
        input.value = item.dataset.label;
        input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');

        items.forEach(function (entry) {
            entry.classList.toggle('bg-green-50', entry === item);
            entry.classList.toggle('font-medium', entry === item);
            entry.classList.toggle('text-green-800', entry === item);
        });

        closeList();
        dispatchChange({
            value: item.dataset.value,
            label: item.dataset.label,
        });
    }

    function visibleItems() {
        return items.filter(function (item) {
            return !item.classList.contains('hidden');
        });
    }

    input.addEventListener('focus', openList);
    input.addEventListener('click', openList);

    input.addEventListener('input', function () {
        hidden.value = '';
        input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        openList();
        dispatchChange({ value: '', label: '' });
    });

    input.addEventListener('keydown', function (event) {
        const shown = visibleItems();

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = Math.min(activeIndex + 1, shown.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
        } else if (event.key === 'Enter') {
            event.preventDefault();

            if (activeIndex >= 0 && shown[activeIndex]) {
                selectItem(shown[activeIndex]);
            }

            return;
        } else if (event.key === 'Escape') {
            closeList();
            return;
        } else {
            return;
        }

        clearActiveItem();

        if (shown[activeIndex]) {
            shown[activeIndex].classList.add('bg-green-100');
            shown[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    });

    list.addEventListener('click', function (event) {
        const item = event.target.closest('li[data-value]');

        if (item) {
            selectItem(item);
        }
    });

    document.addEventListener('click', function (event) {
        if (!root.contains(event.target)) {
            closeList();
        }
    });

    root.addEventListener('searchable-select:filter-kelurahan', function (event) {
        const allowedIds = event.detail?.kelurahanIds;
        const preserveValue = event.detail?.preserveValue === true;
        const allowedSet = allowedIds === null || allowedIds === undefined
            ? null
            : new Set(allowedIds.map(String));

        items.forEach(function (item) {
            const kelurahanId = item.dataset.kelurahanId || '';
            const allowed = allowedSet === null
                || (kelurahanId !== '' && allowedSet.has(kelurahanId));

            item.classList.toggle('wilayah-filtered-out', !allowed);
        });

        if (!preserveValue) {
            const currentItem = items.find(function (item) {
                return item.dataset.value === hidden.value;
            });
            const currentKelurahanId = currentItem?.dataset.kelurahanId;
            const currentAllowed = allowedSet === null
                || (currentKelurahanId && allowedSet.has(currentKelurahanId));

            if (hidden.value && !currentAllowed) {
                hidden.value = '';
                input.value = '';
                input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                dispatchChange({ value: '', label: '' });
            }
        }

        filterList(input.value);
    });

    root.addEventListener('searchable-select:set-value', function (event) {
        const value = String(event.detail?.value ?? '');
        const label = String(event.detail?.label ?? '');

        hidden.value = value;
        input.value = label;
        input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');

        items.forEach(function (item) {
            const selected = item.dataset.value === value;
            item.classList.toggle('bg-green-50', selected);
            item.classList.toggle('font-medium', selected);
            item.classList.toggle('text-green-800', selected);
            item.classList.toggle('text-gray-800', !selected);
        });

        dispatchChange({ value: value, label: label });
    });

    root.dispatchEvent(new CustomEvent('searchable-select:ready', { bubbles: true }));
}

function initSearchableSelects() {
    document.querySelectorAll('[data-searchable-select]').forEach(initSearchableSelect);

    document.querySelectorAll('form').forEach(function (form) {
        if (form.dataset.searchableSubmitBound === 'true') {
            return;
        }

        form.dataset.searchableSubmitBound = 'true';

        form.addEventListener('submit', function (event) {
            form.querySelectorAll('[data-searchable-required="true"]').forEach(function (hidden) {
                const container = hidden.closest('[data-searchable-select]');

                if (!container || container.closest('.hidden')) {
                    return;
                }

                const input = container.querySelector('[data-searchable-input]');

                if (input?.disabled) {
                    return;
                }

                if (!hidden.value) {
                    event.preventDefault();
                    input?.focus();
                    input?.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                }
            });
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearchableSelects);
} else {
    initSearchableSelects();
}
