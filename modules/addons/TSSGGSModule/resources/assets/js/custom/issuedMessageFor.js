/**
 * Settings > SSL certificates > Client Messages: "Issued SSL Message for" shows only the text box of the chosen
 * message (General, Sectigo SSL, DigiCert SSL - App\Libs\IssuedMessage). The selector has no name, so it is not saved.
 */
// leading semicolon: the module joins every script into one bundle
;(function () {
    'use strict';

    function box(form, name) {
        var field = form.querySelector('[name="' + name + '"]');
        return field ? field.closest('.lu-form-group') || field.parentElement : null;
    }

    function apply(select) {
        var form = select.closest('form') || document;
        Array.prototype.forEach.call(select.options, function (option) {
            var group = box(form, option.value);
            if (group) {
                group.style.display = option.value === select.value ? '' : 'none';
            }
        });
    }

    function init() {
        document.querySelectorAll('.tssggs-issued-for__select').forEach(function (select) {
            if (select.getAttribute('data-ready')) {
                return;
            }
            // the text boxes are drawn by the page script after this element: wait until they are there
            var form = select.closest('form') || document;
            if (!box(form, select.value)) {
                return;
            }
            select.setAttribute('data-ready', '1');
            select.addEventListener('change', function () { apply(select); });
            apply(select);
        });
    }

    // the settings form is rendered by Vue after load and again when its section is reopened
    new MutationObserver(init).observe(document.documentElement, {childList: true, subtree: true});
    document.addEventListener('DOMContentLoaded', init);
})();
