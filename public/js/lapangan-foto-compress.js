/**
 * Kompres foto di perangkat sebelum unggah (Input Lapangan).
 * Target: ≤ maxOutputBytes per file, JPEG, sisi terpanjang ≤ maxDimension px.
 */
(function (global) {
    const DEFAULTS = {
        maxOutputBytes: Math.floor(3.9 * 1024 * 1024),
        maxDimension: 1920,
        initialQuality: 0.82,
        minQuality: 0.45,
        skipBelowBytes: 450 * 1024,
    };

    function canvasToBlob(canvas, quality) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) {
                if (blob) {
                    resolve(blob);
                } else {
                    reject(new Error('toBlob failed'));
                }
            }, 'image/jpeg', quality);
        });
    }

    function loadImageFromFile(file) {
        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('load failed'));
            };
            img.src = url;
        });
    }

    function scaledDimensions(width, height, maxDimension) {
        const longest = Math.max(width, height);
        if (longest <= maxDimension) {
            return { width: width, height: height };
        }
        const scale = maxDimension / longest;

        return {
            width: Math.max(1, Math.round(width * scale)),
            height: Math.max(1, Math.round(height * scale)),
        };
    }

    function outputFileName(originalName) {
        const base = (originalName || 'foto').replace(/\.[^.\\/]+$/, '');

        return base + '.jpg';
    }

    function setFileOnInput(input, file) {
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    }

    /**
     * @param {File} file
     * @param {Partial<typeof DEFAULTS>} options
     * @returns {Promise<File>}
     */
    async function compressFile(file, options) {
        const opts = { ...DEFAULTS, ...options };

        if (!file || !file.type || !file.type.startsWith('image/')) {
            return file;
        }

        if (file.size <= opts.skipBelowBytes && file.type === 'image/jpeg') {
            return file;
        }

        const img = await loadImageFromFile(file);
        let { width, height } = scaledDimensions(img.naturalWidth || img.width, img.naturalHeight || img.height, opts.maxDimension);
        let quality = opts.initialQuality;
        let blob = null;

        for (let attempt = 0; attempt < 8; attempt++) {
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error('canvas unsupported');
            }
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);
            ctx.drawImage(img, 0, 0, width, height);

            blob = await canvasToBlob(canvas, quality);

            if (blob.size <= opts.maxOutputBytes) {
                break;
            }

            if (quality > opts.minQuality + 0.05) {
                quality = Math.max(opts.minQuality, quality - 0.1);
            } else {
                width = Math.max(640, Math.round(width * 0.85));
                height = Math.max(640, Math.round(height * 0.85));
                quality = opts.initialQuality;
            }
        }

        if (!blob) {
            throw new Error('compress failed');
        }

        return new File([blob], outputFileName(file.name), {
            type: 'image/jpeg',
            lastModified: Date.now(),
        });
    }

    /**
     * @param {HTMLFormElement} form
     * @param {string[]} fieldNames
     * @param {(field: string, index: number, total: number) => void} [onProgress]
     */
    async function compressFormFotoInputs(form, fieldNames, onProgress) {
        const total = fieldNames.length;

        for (let index = 0; index < fieldNames.length; index++) {
            const field = fieldNames[index];
            const input = form.querySelector('#' + field);
            if (!input?.files?.length) {
                continue;
            }

            if (typeof onProgress === 'function') {
                onProgress(field, index, total);
            }

            const original = input.files[0];
            const compressed = await compressFile(original);
            setFileOnInput(input, compressed);
        }
    }

    global.LapanganFotoCompress = {
        compressFile: compressFile,
        compressFormFotoInputs: compressFormFotoInputs,
        setFileOnInput: setFileOnInput,
        defaults: DEFAULTS,
    };
})(typeof window !== 'undefined' ? window : globalThis);
