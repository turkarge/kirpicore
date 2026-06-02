(function () {
    'use strict';

    if (!('serviceWorker' in navigator)) {
        return;
    }

    var isSecureContext = window.location.protocol === 'https:'
        || window.location.hostname === 'localhost'
        || window.location.hostname === '127.0.0.1';

    if (!isSecureContext) {
        return;
    }

    window.addEventListener('load', function () {
        var baseUrl = (window.KIRPI_CONFIG && window.KIRPI_CONFIG.baseUrl) ? window.KIRPI_CONFIG.baseUrl : '/';
        if (baseUrl.slice(-1) !== '/') {
            baseUrl += '/';
        }

        navigator.serviceWorker.register(baseUrl + 'service-worker.js', { scope: baseUrl })
            .catch(function (error) {
                if (window.console && window.console.warn) {
                    window.console.warn('Kirpi PWA service worker registration failed:', error);
                }
            });
    });
})();
