/**
 * Settings > SSL certificates > Client Messages: "Issued SSL Message for" + "Language" show only the boxes of the chosen
 * certificate family (its "Show this message" switch and its text in that language - App\Libs\Certificates\IssuedMessage).
 * The selectors have no name, so they are not saved.
 */
// leading semicolon: the module joins every script into one bundle
;(function () {
    'use strict';

    function box(form, name) {
        var field = form.querySelector('[name="' + name + '"]');
        return field ? field.closest('.mod-field') || field.parentElement : null;
    }

    function apply(wrap) {
        var form     = wrap.closest('form') || document;
        var family   = wrap.querySelector('.tssggs-issued-for__select').value;
        var language = wrap.querySelector('.tssggs-issued-for__language-select').value;

        form.querySelectorAll('[name^="issuedMessage"]').forEach(function (field) {
            var name  = field.getAttribute('name');
            var group = box(form, name);
            if (!group) {
                return;
            }
            var show = name === 'issuedMessageOn_' + family || name === 'issuedMessage_' + family + '_' + language;
            group.style.display = show ? '' : 'none';
        });
    }

    function init() {
        document.querySelectorAll('.tssggs-issued-for').forEach(function (wrap) {
            if (wrap.getAttribute('data-ready')) {
                return;
            }
            // the text boxes are drawn by the page script after this element: wait until they are there
            var form  = wrap.closest('form') || document;
            var first = wrap.querySelector('.tssggs-issued-for__select').value;
            if (!box(form, 'issuedMessageOn_' + first)) {
                return;
            }
            wrap.setAttribute('data-ready', '1');
            wrap.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () { apply(wrap); });
            });
            apply(wrap);
        });
    }

    // the settings form is rendered by Vue after load and again when its section is reopened
    new MutationObserver(init).observe(document.documentElement, {childList: true, subtree: true});
    document.addEventListener('DOMContentLoaded', init);
})();
