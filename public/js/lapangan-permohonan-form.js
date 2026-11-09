(function () {
    const FORM_ID = 'lapangan-permohonan-progres-form';
    const MAX_PER_FILE_BYTES = 4 * 1024 * 1024;
    const MAX_TOTAL_BYTES = 24 * 1024 * 1024;

    function getForm() {
        return document.getElementById(FORM_ID);
    }

    function fotoFieldNames(form) {
        const raw = form.dataset.fotoFields || '[]';

        try {
            return JSON.parse(raw);
        } catch (error) {
            return [];
        }
    }

    function shouldCollectFoto(form) {
        return form.dataset.collectFoto === '1';
    }

    function validateFoto(form, checkSizes) {
        if (!shouldCollectFoto(form)) {
            return [];
        }

        const errors = [];
        const fotoFields = fotoFieldNames(form);
        const fotoSection = document.getElementById('foto-dokumentasi-section');

        if (fotoSection?.classList.contains('hidden')) {
            return [];
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
                errors.push(label + ' masih terlalu besar (' + sizeMb + ' MB) setelah kompresi.');
            }
        });

        if (checkSizes && totalBytes > MAX_TOTAL_BYTES) {
            const totalMb = (totalBytes / (1024 * 1024)).toFixed(1);
            errors.push('Total semua foto ' + totalMb + ' MB (maks. 24 MB).');
        }

        return errors;
    }

    function showErrors(messages) {
        const errorBox = document.getElementById('lapangan-permohonan-errors');
        const errorList = errorBox?.querySelector('[data-error-list]');

        if (!errorBox || !errorList) {
            return;
        }

        if (messages.length === 0) {
            errorBox.classList.add('hidden');
            errorList.innerHTML = '';

            return;
        }

        errorList.innerHTML = messages.map(function (m) {
            return '<li>' + m + '</li>';
        }).join('');
        errorBox.classList.remove('hidden');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function getSubmitButton(form) {
        return form.querySelector('.lapangan-form-actions button[type="submit"]');
    }

    function setSubmitBusy(button, label) {
        if (!button) {
            return;
        }

        button.disabled = true;
        button.dataset.originalLabel = button.textContent;
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

    function submitFormNative(form) {
        form.dataset.lapanganNativeSubmit = '1';
        form.submit();
    }

    function bindForm(form) {
        if (form.dataset.lapanganPermohonanBound === '1') {
            return;
        }

        form.dataset.lapanganPermohonanBound = '1';

        if (!shouldCollectFoto(form)) {
            return;
        }

        form.addEventListener('submit', function (event) {
            if (form.dataset.lapanganNativeSubmit === '1') {
                delete form.dataset.lapanganNativeSubmit;

                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            event.preventDefault();

            let errors = validateFoto(form, false);
            showErrors(errors);

            if (errors.length > 0) {
                return;
            }

            const submitButton = getSubmitButton(form);
            const compressApi = window.LapanganFotoCompress;

            if (!compressApi?.compressFormFotoInputs) {
                errors = validateFoto(form, true);
                showErrors(errors);

                if (errors.length > 0) {
                    return;
                }

                submitFormNative(form);

                return;
            }

            setSubmitBusy(submitButton, 'Mengompres foto…');

            compressApi.compressFormFotoInputs(form, fotoFieldNames(form))
                .then(function () {
                    errors = validateFoto(form, true);
                    showErrors(errors);

                    if (errors.length > 0) {
                        resetSubmitButton(submitButton);

                        return;
                    }

                    submitFormNative(form);
                })
                .catch(function () {
                    resetSubmitButton(submitButton);
                    showErrors([
                        'Foto tidak dapat diproses di perangkat ini. Coba format JPG/PNG atau kompres manual di galeri HP.',
                    ]);
                });
        });
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
