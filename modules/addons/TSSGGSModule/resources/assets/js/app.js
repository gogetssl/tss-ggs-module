/*
 * The module's own script for pages on the new kernel (htmx + Alpine.js, no build step - edit this file directly).
 *
 * - every htmx request carries the WHMCS token (the layout prints it as data-token on #layers2), so a POST action
 *   is accepted by the kernel
 * - a failed request shows its answer instead of failing silently
 */
(function () {
    'use strict';

    function token() {
        var root = document.getElementById('layers2');
        return root ? root.getAttribute('data-token') || '' : '';
    }

    document.addEventListener('htmx:configRequest', function (event) {
        event.detail.headers['X-CSRF-Token'] = token();
    });

    // the kernel answers an error (expired token, exception) with a notice: show it in the target too
    document.addEventListener('htmx:beforeSwap', function (event) {
        if (event.detail.xhr.status >= 400 && event.detail.xhr.responseText) {
            event.detail.shouldSwap = true;
            event.detail.isError = false;
        }
    });

    // while a part of the page loads, its container gets .tssggs-loading (theme.css dims it)
    document.addEventListener('htmx:beforeRequest', function (event) {
        var target = event.detail.target;
        if (target) { target.classList.add('tssggs-loading'); }
    });
    document.addEventListener('htmx:afterRequest', function (event) {
        var target = event.detail.target;
        if (target) { target.classList.remove('tssggs-loading'); }
    });
})();
