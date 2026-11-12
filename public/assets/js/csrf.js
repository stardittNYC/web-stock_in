/*
 * Global CSRF helper.
 *
 * The original app ran on plain PHP without CSRF protection. Laravel enables
 * CSRF for all web (non-GET) requests, so this shim transparently attaches the
 * token to every same-origin request made with fetch() — no per-call changes
 * needed in the individual page scripts.
 */
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');

    if (!meta || typeof window.fetch !== 'function') {
        return;
    }

    var token = meta.getAttribute('content');
    var originalFetch = window.fetch.bind(window);

    window.fetch = function (resource, options) {
        options = options || {};

        try {
            var method = (options.method || (resource && resource.method) || 'GET').toUpperCase();
            var url = typeof resource === 'string' ? resource : ((resource && resource.url) || '');
            var isSameOrigin = url === '' || !/^https?:\/\//i.test(url) || url.indexOf(window.location.origin) === 0;

            if (isSameOrigin && ['GET', 'HEAD', 'OPTIONS'].indexOf(method) === -1) {
                var headers = new Headers(options.headers || {});

                if (!headers.has('X-CSRF-TOKEN')) {
                    headers.set('X-CSRF-TOKEN', token);
                }

                options.headers = headers;

                if (options.body instanceof FormData && !options.body.has('_token')) {
                    options.body.append('_token', token);
                }
            }
        } catch (e) {
            // Ignore and fall through to the original fetch call.
        }

        return originalFetch(resource, options);
    };
})();
