(function () {
    function parseJson(value, fallback) {
        try {
            return JSON.parse(value || '');
        } catch (error) {
            return fallback;
        }
    }

    function initPetugasPicker(root) {
        if (!root || root.dataset.initialized === '1') {
            return;
        }

        root.dataset.initialized = '1';

        let roster = parseJson(root.dataset.initialRoster, []);
        let selected = new Set(parseJson(root.dataset.initialSelected, []));
        const rosterUrl = root.dataset.rosterUrl || '';
        const timSelectId = root.dataset.timSelectId || '';
        const fixedTeams = parseJson(root.dataset.fixedTeams, []);
        const required = root.dataset.required === '1';
        let awaitingTeam = root.dataset.awaitingTeam === '1';
        let currentQuery = '';

        const activeWrap = root.querySelector('[data-petugas-active-wrap]');
        const fieldInput = root.querySelector('[data-petugas-field]');
        const dropdown = root.querySelector('[data-petugas-dropdown]');
        const manualFallback = root.querySelector('[data-petugas-manual-fallback]');
        const countLabel = root.querySelector('[data-petugas-count]');
        const inputsHost = root.querySelector('[data-petugas-inputs]');
        const personilInput = root.querySelector('[data-petugas-personil]');

        function rosterMap() {
            const map = new Map();
            roster.forEach(function (item) {
                map.set(item.id, item);
            });
            return map;
        }

        function selectedNames() {
            const map = rosterMap();
            return Array.from(selected)
                .sort(function (a, b) { return a - b; })
                .map(function (id) { return map.get(id)?.nama; })
                .filter(Boolean);
        }

        function committedPrefix() {
            const names = selectedNames();
            return names.length > 0 ? names.join(';') + ';' : '';
        }

        function buildFieldValue() {
            return committedPrefix() + currentQuery;
        }

        function syncField() {
            if (!fieldInput) {
                return;
            }

            fieldInput.value = buildFieldValue();
            fieldInput.setSelectionRange(fieldInput.value.length, fieldInput.value.length);
        }

        function parseFieldInput() {
            if (!fieldInput) {
                return;
            }

            const value = fieldInput.value;
            const prefix = committedPrefix();

            if (prefix === '') {
                currentQuery = value;
                return;
            }

            if (!value.startsWith(prefix)) {
                fieldInput.value = buildFieldValue();
                fieldInput.setSelectionRange(fieldInput.value.length, fieldInput.value.length);
                return;
            }

            currentQuery = value.slice(prefix.length);
        }

        function syncInputs() {
            if (inputsHost) {
                inputsHost.innerHTML = '';
                Array.from(selected).sort(function (a, b) { return a - b; }).forEach(function (id) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'petugas_ids[]';
                    input.value = String(id);
                    inputsHost.appendChild(input);
                });
            }

            if (countLabel) {
                countLabel.textContent = String(selected.size);
            }

            if (personilInput) {
                personilInput.value = selected.size > 0 ? String(selected.size) : '';
                personilInput.disabled = roster.length === 0;
            }

            syncField();
        }

        function togglePickerMode(hasRoster) {
            activeWrap?.classList.toggle('hidden', !hasRoster && !awaitingTeam);
            manualFallback?.classList.toggle('hidden', hasRoster || awaitingTeam);

            if (fieldInput) {
                fieldInput.disabled = awaitingTeam || !hasRoster;
                fieldInput.placeholder = awaitingTeam
                    ? 'Pilih tim pelaksana terlebih dahulu'
                    : (hasRoster ? 'Ketik sebagian nama, pilih dari daftar. Beberapa nama dipisah ;' : 'Daftar petugas belum diatur');
            }
        }

        function hideDropdown() {
            dropdown?.classList.add('hidden');
            if (dropdown) {
                dropdown.innerHTML = '';
            }
        }

        function matchingItems(query) {
            const normalized = query.trim().toLowerCase();

            return roster.filter(function (item) {
                if (selected.has(item.id)) {
                    return false;
                }

                if (normalized === '') {
                    return true;
                }

                return String(item.nama).toLowerCase().includes(normalized);
            });
        }

        function renderDropdown() {
            if (!dropdown || !fieldInput) {
                return;
            }

            const matches = matchingItems(currentQuery);
            dropdown.innerHTML = '';

            if (awaitingTeam || roster.length === 0) {
                hideDropdown();
                return;
            }

            if (matches.length === 0) {
                dropdown.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500">Tidak ada petugas cocok.</li>';
                dropdown.classList.remove('hidden');
                return;
            }

            matches.forEach(function (item) {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'block w-full px-4 py-2.5 text-left text-sm hover:bg-green-50';
                option.innerHTML = [
                    '<span class="font-semibold text-gray-900">' + item.nama + '</span>',
                    item.jabatan ? '<span class="ml-2 text-xs text-gray-500">' + item.jabatan + '</span>' : '',
                ].join('');

                option.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                });

                option.addEventListener('click', function () {
                    selected.add(item.id);
                    currentQuery = '';
                    hideDropdown();
                    syncInputs();
                    fieldInput.focus();
                });

                dropdown.appendChild(option);
            });

            dropdown.classList.remove('hidden');
        }

        function addFromQueryExact() {
            const matches = matchingItems(currentQuery);
            if (matches.length === 1) {
                selected.add(matches[0].id);
                currentQuery = '';
                hideDropdown();
                syncInputs();
            }
        }

        function removeLastSelected() {
            const ids = Array.from(selected).sort(function (a, b) { return a - b; });
            if (ids.length === 0) {
                return;
            }

            selected.delete(ids[ids.length - 1]);
            currentQuery = '';
            syncInputs();
        }

        fieldInput?.addEventListener('input', function () {
            parseFieldInput();
            renderDropdown();
        });

        fieldInput?.addEventListener('focus', function () {
            parseFieldInput();
            fieldInput.setSelectionRange(fieldInput.value.length, fieldInput.value.length);
            renderDropdown();
        });

        fieldInput?.addEventListener('click', function () {
            fieldInput.setSelectionRange(fieldInput.value.length, fieldInput.value.length);
        });

        fieldInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addFromQueryExact();
                return;
            }

            if (event.key === 'Escape') {
                hideDropdown();
                return;
            }

            if (event.key === 'Backspace' && currentQuery === '') {
                const prefix = committedPrefix();
                if (selected.size > 0 && fieldInput.value === prefix) {
                    event.preventDefault();
                    removeLastSelected();
                }
            }
        });

        fieldInput?.addEventListener('blur', function () {
            setTimeout(function () {
                if (!root.contains(document.activeElement)) {
                    addFromQueryExact();
                    hideDropdown();
                    currentQuery = '';
                    syncInputs();
                }
            }, 150);
        });

        root.addEventListener('petugas-picker:commit-query', function () {
            addFromQueryExact();
            syncInputs();
        });

        root.querySelector('[data-petugas-clear]')?.addEventListener('click', function () {
            selected.clear();
            currentQuery = '';
            hideDropdown();
            syncInputs();
            fieldInput?.focus();
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                hideDropdown();
            }
        });

        async function loadRosterForTeams(teams) {
            if (!teams.length || !rosterUrl) {
                return;
            }

            const params = new URLSearchParams();
            teams.forEach(function (team) {
                params.append('teams[]', team);
            });

            const response = await fetch(rosterUrl + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                roster = [];
                awaitingTeam = false;
                selected.clear();
                currentQuery = '';
                togglePickerMode(false);
                syncInputs();
                hideDropdown();
                return;
            }

            const payload = await response.json();
            roster = payload.petugas || [];
            awaitingTeam = false;
            selected.forEach(function (id) {
                if (!roster.some(function (item) { return item.id === id; })) {
                    selected.delete(id);
                }
            });
            togglePickerMode(roster.length > 0);
            syncInputs();
            hideDropdown();
        }

        if (timSelectId) {
            const timSelect = document.getElementById(timSelectId);
            timSelect?.addEventListener('change', function () {
                const team = timSelect.value;
                if (!team) {
                    roster = [];
                    awaitingTeam = true;
                    selected.clear();
                    currentQuery = '';
                    togglePickerMode(false);
                    syncInputs();
                    hideDropdown();
                    return;
                }

                awaitingTeam = false;
                loadRosterForTeams([team]);
            });

            if (timSelect?.value) {
                awaitingTeam = false;
            }
        }

        root.closest('form')?.addEventListener('submit', function (event) {
            const form = event.currentTarget;
            if (form?.id === 'lapangan-pemeliharaan-form' || form?.dataset.lapanganSubmitOk === '1') {
                return;
            }

            if (!required || roster.length === 0) {
                return;
            }

            if (selected.size === 0) {
                event.preventDefault();
                fieldInput?.focus();
                fieldInput?.classList.add('border-red-500', 'ring-2', 'ring-red-200');
                fieldInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        togglePickerMode(roster.length > 0);
        syncInputs();

        if (fixedTeams.length > 0 && roster.length === 0 && !awaitingTeam) {
            loadRosterForTeams(fixedTeams);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.petugas-picker').forEach(initPetugasPicker);
    });
})();
