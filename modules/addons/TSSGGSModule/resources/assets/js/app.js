/*
 * The module's own script for pages on the new kernel (htmx + Alpine.js, no build step - edit this file directly).
 *
 * - every htmx request carries the WHMCS token (the layout prints it as data-token on #layers2), so a POST action
 *   is accepted by the kernel
 * - a question before a change, in the module's own dialog (the <template data-confirm-template> of
 *   admin/layout.tpl):
 *       hx-confirm="question"      on an htmx element
 *       data-confirm="question"    on a form or a submit button (data-no-confirm on a button of such a form: no question)
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
        // a button of the form that does something else and needs no question: <button data-no-confirm>
        if (submitter && submitter.hasAttribute('data-no-confirm')) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        ask(form, form.getAttribute('data-confirm'), function () {
            form.confirmed = true;
            if (form.requestSubmit) { form.requestSubmit(submitter); } else { form.submit(); }
        });
    }, true);

    /* ---- dialogs with a form (admin/partials/dialog_start.tpl, put into the layout's dialog place) ---- */

    function dialogPlace() {
        return root() ? root().querySelector('[data-dialog]') : null;
    }

    function closeDialog() {
        var dialog = dialogPlace();
        if (dialog && dialog.parentNode) {
            dialog.parentNode.textContent = '';
        }
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest) {
            return;
        }
        // the X, Cancel, or a click on the dark area around the dialog
        if (event.target.closest('[data-dialog-close]') || event.target === dialogPlace()) {
            closeDialog();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && dialogPlace() && !document.querySelector('.lu-selectize-dropdown[style*="block"]')) {
            closeDialog();
        }
    });
    // the action says when the dialog's work is done (Response::closesDialog)
    document.addEventListener('htmx:afterRequest', function (event) {
        if (event.detail.xhr && event.detail.xhr.getResponseHeader('X-Module-Dialog') === 'close') {
            closeDialog();
        }
    });

    /* ---- settings screens: the Save bar (core/savebar.js) learns how the save went ---- */

    document.addEventListener('htmx:afterSettle', function (event) {
        var config = event.detail.requestConfig;
        var form = document.querySelector('#layers2 form[data-saved]');
        // Test Connection ([data-test]) saves nothing and leaves the form as it is: only its result is brought into view
        if (config && config.elt && config.elt.hasAttribute && config.elt.hasAttribute('data-test')) {
            var tested = event.detail.target && event.detail.target.querySelector ? event.detail.target.querySelector('[role=status]') : null;
            if (tested && tested.scrollIntoView) { tested.scrollIntoView({ block: 'center' }); }
            return;
        }
        if (form && config && String(config.verb).toLowerCase() === 'post') {
            document.dispatchEvent(new CustomEvent('tssggs-form-result', { detail: { ok: form.getAttribute('data-saved') === '1' } }));
            var result = form.querySelector('[role=status]');
            if (result && result.scrollIntoView) { result.scrollIntoView({ block: 'center' }); }
        }
    });

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
