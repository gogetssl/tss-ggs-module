/*
 * The module's own script for pages on the new kernel (htmx + Alpine.js, no build step - edit this file directly).
 *
 * - every htmx request carries the WHMCS token (the layout prints it as data-token on #layers2), so a POST action
 *   is accepted by the kernel
 * - a question before a change, in the module's own dialog (the <template data-confirm-template> of
 *   admin/layout.tpl):
 *       hx-confirm="question"      on an htmx element
 *       data-confirm="question"    on a form or a submit button
 *   data-confirm-title="..." sets the dialog's title (default: the page title)
 * - a failed request shows its answer instead of failing silently
 * - while a part of the page loads, its container is dimmed
 */
(function () {
    'use strict';

    function root() {
        return document.getElementById('layers2');
    }

    function token() {
        return root() ? root().getAttribute('data-token') || '' : '';
    }

    document.addEventListener('htmx:configRequest', function (event) {
        event.detail.headers['X-CSRF-Token'] = token();
    });

    /* ---- confirmation dialog ---- */

    function ask(element, question, yes) {
        var template = document.querySelector('template[data-confirm-template]');
        var host = root() ? root().querySelector('.lu-app') : null;
        if (!template || !host) {
            if (window.confirm(question)) { yes(); }
            return;
        }

        var modal = template.content.firstElementChild.cloneNode(true);
        var heading = document.querySelector('#layers2 h2');
        modal.querySelector('[data-confirm-title]').textContent = (element && element.getAttribute('data-confirm-title')) || (heading ? heading.textContent : '');
        modal.querySelector('[data-confirm-text]').textContent = question;

        function close() {
            document.removeEventListener('keydown', onKey);
            modal.remove();
        }
        function onKey(event) {
            if (event.key === 'Escape') { close(); }
        }
        modal.addEventListener('click', function (event) {
            if (event.target.closest('[data-confirm-yes]')) {
                close();
                yes();
            } else if (event.target.closest('[data-confirm-no]') || event.target === modal) {
                close();
            }
        });
        document.addEventListener('keydown', onKey);
        host.appendChild(modal);
        modal.querySelector('[data-confirm-yes]').focus();
    }

    // hx-confirm
    document.addEventListener('htmx:confirm', function (event) {
        if (!event.detail.question) {
            return;
        }
        event.preventDefault();
        ask(event.detail.elt, event.detail.question, function () {
            event.detail.issueRequest(true);
        });
    });

    // <button data-confirm> (a form with several buttons, each with its own question)
    document.addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('button[data-confirm]') : null;
        if (!button) {
            return;
        }
        if (button.confirmed) {
            button.confirmed = false;
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        ask(button, button.getAttribute('data-confirm'), function () {
            button.confirmed = true;
            button.click();
        });
    }, true);

    // <form data-confirm>
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.getAttribute || !form.getAttribute('data-confirm')) {
            return;
        }
        if (form.confirmed) {
            form.confirmed = false;
            return;
        }
        var submitter = event.submitter || null;
        event.preventDefault();
        event.stopImmediatePropagation();
        ask(form, form.getAttribute('data-confirm'), function () {
            form.confirmed = true;
            if (form.requestSubmit) { form.requestSubmit(submitter); } else { form.submit(); }
        });
    }, true);

    /* ---- htmx answers ---- */

    // Only the kernel's own answers go into the page (header X-Module-Kernel): its errors (expired token, exception)
    // are notices and are shown like any other answer. Anything else - an error page of WHMCS or of the web server -
    // is kept out; a part that loads by itself (hx-trigger="load") is asked for once more.
    document.addEventListener('htmx:beforeSwap', function (event) {
        var xhr = event.detail.xhr;
        if (xhr.getResponseHeader('X-Module-Kernel')) {
            if (xhr.status >= 400 && xhr.responseText) {
                event.detail.shouldSwap = true;
                event.detail.isError = false;
            }
            return;
        }
        event.detail.shouldSwap = false;
        var element = event.detail.requestConfig ? event.detail.requestConfig.elt : null;
        if (element && /\bload\b/.test(element.getAttribute('hx-trigger') || '') && !element.askedAgain) {
            element.askedAgain = true;
            window.setTimeout(function () { window.htmx.trigger(element, 'load'); }, 800);
        }
    });

    // while a part of the page loads, its container gets a "loading" class (theme.css dims it)
    document.addEventListener('htmx:beforeRequest', function (event) {
        var target = event.detail.target;
        if (target) { target.classList.add('tssggs-loading'); }
    });
    document.addEventListener('htmx:afterRequest', function (event) {
        var target = event.detail.target;
        if (target) { target.classList.remove('tssggs-loading'); }
    });
})();
