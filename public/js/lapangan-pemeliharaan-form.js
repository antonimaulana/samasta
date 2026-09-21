(function () {
    const FORM_ID = 'lapangan-pemeliharaan-form';

    function getForm() {
        return document.getElementById(FORM_ID);
    }

    function petugasIdsCount(form) {
        return form.querySelectorAll('input[name="petugas_ids[]"]').length;
    }

    function manualPersonilFilled(form) {
        const manual = form.querySelector('[data-petugas-manual-fallback]:not(.hidden) input[name="jumlah_personil"]');
        return manual && Number(manual.value) >= 1;
    }

    function fotoFieldNames(form) {
        const raw = form.dataset.fotoFields || '[]';

        try {
            return JSON.parse(raw);
        } catch (error) {
            return [];
        }
    }

    function validate(form) {
        const errors = [];
        const fotoFields = fotoFieldNames(form);

        const tanggal = form.querySelector('#tanggal');
        if (!tanggal?.value) {
            errors.push('Isi tanggal & waktu pelaksanaan.');
        }

        const lokasiLuar = form.querySelector('#lokasi_luar')?.checked ?? false;
        const tamanId = form.querySelector('#taman_id')?.value ?? '';
        const lokasiManual = form.querySelector('#lokasi_pelaksanaan_manual')?.value?.trim() ?? '';

        if (lokasiLuar) {
            if (!lokasiManual) {
                errors.push('Isi alamat lokasi (luar wilayah pemeliharaan).');
            }
        } else if (!tamanId) {
            errors.push('Pilih lokasi taman: ketuk nama taman dari daftar (bukan hanya mengetik).');
        }

        const picker = form.querySelector('.petugas-picker');
        const rosterRequired = picker?.dataset.required === '1';
        const activeWrap = picker?.querySelector('[data-petugas-active-wrap]');
        const hasRosterUi = activeWrap && !activeWrap.classList.contains('hidden');

        if (rosterRequired && hasRosterUi && petugasIdsCount(form) === 0) {
            errors.push('Pilih minimal satu petugas pelaksana (ketuk nama dari daftar).');
        } else if (rosterRequired && !hasRosterUi && !manualPersonilFilled(form)) {
            errors.push('Isi jumlah personil.');
        }

        fotoFields.forEach(function (field) {
            const input = form.querySelector('#' + field);
            if (!input) {
                return;
            }

            if (!input.files || input.files.length === 0) {
                const label = input.closest('div')?.querySelector('label')?.textContent?.trim() || field;
                errors.push('Unggah foto: ' + label + '.');
            }
        });

        let totalBytes = 0;
        fotoFields.forEach(function (field) {
            const file = form.querySelector('#' + field)?.files?.[0];
            if (file) {
                totalBytes += file.size;
            }
        });

        if (totalBytes > 18 * 1024 * 1024) {
            errors.push('Total ukuran foto terlalu besar (max ~18 MB). Kompres foto atau unggah resolusi lebih kecil.');
        }

        return errors;
    }

    function showErrors(form, messages) {
        const errorBox = document.getElementById('lapangan-pemeliharaan-errors');
        const errorList = errorBox?.querySelector('[data-error-list]');

        if (!errorBox || !errorList) {
            if (messages.length > 0) {
                window.alert(messages.join('\n'));
            }

            return;
        }

        errorList.innerHTML = '';
        messages.forEach(function (message) {
            const li = document.createElement('li');
            li.textContent = message;
            errorList.appendChild(li);
        });
        errorBox.classList.toggle('hidden', messages.length === 0);

        if (messages.length > 0) {
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function commitPetugasPickers(form) {
        form.querySelectorAll('.petugas-picker').forEach(function (root) {
            root.dispatchEvent(new CustomEvent('petugas-picker:commit-query', { bubbles: true }));
        });
    }

    function focusFirstIssue(form, errors, fotoFields) {
        if (errors.some(function (message) { return message.includes('taman'); })) {
            const tamanSearch = form.querySelector('#taman_id_search');
            tamanSearch?.focus();
            tamanSearch?.classList.add('border-red-500', 'ring-2', 'ring-red-200');
        }

        if (errors.some(function (message) { return message.includes('petugas') || message.includes('personil'); })) {
            form.querySelector('[data-petugas-field]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        const firstMissingFoto = fotoFields.find(function (field) {
            const input = form.querySelector('#' + field);
            return input && (!input.files || input.files.length === 0);
        });

        if (firstMissingFoto) {
            form.querySelector('#' + firstMissingFoto)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function bindForm(form) {
        if (form.dataset.lapanganPemeliharaanBound === '1') {
            return;
        }

        form.dataset.lapanganPemeliharaanBound = '1';
        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', function (event) {
            delete form.dataset.lapanganSubmitOk;

            commitPetugasPickers(form);

            const errors = validate(form);
            showErrors(form, errors);

            if (errors.length > 0) {
                event.preventDefault();
                event.stopImmediatePropagation();
                focusFirstIssue(form, errors, fotoFieldNames(form));

                return;
            }

            form.dataset.lapanganSubmitOk = '1';

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                submitButton.disabled = true;
                submitButton.dataset.originalLabel = submitButton.dataset.originalLabel || submitButton.textContent;
                submitButton.textContent = 'Menyimpan…';
            }
        }, true);
    }

    function init() {
        const form = getForm();
        if (form) {
            bindForm(form);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
