(function () {
    const FORM_ID = 'lapangan-pemeliharaan-form';
    const MAX_PER_FILE_BYTES = 4 * 1024 * 1024;
    const MAX_TOTAL_BYTES = 24 * 1024 * 1024;

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

    function validate(form, options) {
        const checkSizes = options?.checkSizes !== false;
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

        let totalBytes = 0;

        fotoFields.forEach(function (field) {
            const input = form.querySelector('#' + field);
            if (!input) {
                return;
            }

            const label = input.closest('div')?.querySelector('label')?.textContent?.trim() || field;

            if (!input.files || input.files.length === 0) {
                errors.push('Unggah foto: ' + label + '.');
                return;
            }

            if (!checkSizes) {
                return;
            }

            const file = input.files[0];
            totalBytes += file.size;

            if (file.size > MAX_PER_FILE_BYTES) {
                const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
                errors.push(label + ' masih terlalu besar (' + sizeMb + ' MB) setelah kompresi. Ambil ulang dengan resolusi lebih kecil.');
            }
        });

        if (checkSizes && totalBytes > MAX_TOTAL_BYTES) {
            const totalMb = (totalBytes / (1024 * 1024)).toFixed(1);
            errors.push('Total semua foto ' + totalMb + ' MB (maks. 24 MB). Coba kurangi resolusi kamera.');
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

    function getSubmitButton(form) {
        return form.querySelector('button[type="submit"]');
    }

    function setSubmitBusy(button, label) {
        if (!button || button.disabled) {
            return;
        }
        button.disabled = true;
        button.dataset.originalLabel = button.dataset.originalLabel || button.textContent;
        button.textContent = label;
    }

    function resetSubmitButton(button) {
        if (!button) {
            return;
        }
        button.disabled = false;
        if (button.dataset.originalLabel) {
            button.textContent = button.dataset.originalLabel;
        }
    }

    function bindForm(form) {
        if (form.dataset.lapanganPemeliharaanBound === '1') {
            return;
        }

        form.dataset.lapanganPemeliharaanBound = '1';
        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', function (event) {
            if (form.dataset.lapanganNativeSubmit === '1') {
                delete form.dataset.lapanganNativeSubmit;

                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            delete form.dataset.lapanganSubmitOk;

            commitPetugasPickers(form);

            const fotoFields = fotoFieldNames(form);
            let errors = validate(form, { checkSizes: false });
            showErrors(form, errors);

            if (errors.length > 0) {
                focusFirstIssue(form, errors, fotoFields);

                return;
            }

            const submitButton = getSubmitButton(form);
            const compressApi = window.LapanganFotoCompress;

            if (!compressApi?.compressFormFotoInputs) {
                errors = validate(form, { checkSizes: true });
                showErrors(form, errors);
                if (errors.length > 0) {
                    focusFirstIssue(form, errors, fotoFields);

                    return;
                }
                form.dataset.lapanganNativeSubmit = '1';
                setSubmitBusy(submitButton, 'Menyimpan…');
                form.submit();

                return;
            }

            setSubmitBusy(submitButton, 'Mengompres foto…');

            compressApi.compressFormFotoInputs(form, fotoFields)
                .then(function () {
                    errors = validate(form, { checkSizes: true });
                    showErrors(form, errors);

                    if (errors.length > 0) {
                        resetSubmitButton(submitButton);
                        focusFirstIssue(form, errors, fotoFields);

                        return;
                    }

                    form.dataset.lapanganSubmitOk = '1';
                    form.dataset.lapanganNativeSubmit = '1';
                    setSubmitBusy(submitButton, 'Menyimpan…');
                    form.submit();
                })
                .catch(function () {
                    resetSubmitButton(submitButton);
                    showErrors(form, [
                        'Foto tidak dapat diproses di perangkat ini. Coba format JPG/PNG, atau kompres manual di galeri HP.',
                    ]);
                });
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
