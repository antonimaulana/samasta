(function () {
    const refreshUrl = document.body?.dataset?.lapanganCsrfUrl;

    if (!refreshUrl) {
        return;
    }

    function applyToken(token) {
        if (!token) {
            return;
        }

        document.querySelectorAll('input[name="_token"]').forEach(function (input) {
            input.value = token;
        });

        const meta = document.querySelector('meta[name="csrf-token"]');

        if (meta) {
            meta.setAttribute('content', token);
        }
    }

    function refreshCsrf() {
        return fetch(refreshUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (response) {
                if (response.status === 401 || response.status === 403) {
                    return null;
                }

                if (!response.ok) {
                    return null;
                }

                return response.json();
            })
            .then(function (data) {
                if (data?.token) {
                    applyToken(data.token);
                }

                return data;
            })
            .catch(function () {
                return null;
            });
    }

    window.lapanganRefreshCsrf = refreshCsrf;

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            refreshCsrf();
        }
    });

    window.setInterval(refreshCsrf, 15 * 60 * 1000);
})();
