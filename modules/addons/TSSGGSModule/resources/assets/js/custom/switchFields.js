/**
 * Settings: fields that belong to a Yes / No switch are shown only while it is on (e.g. General > LEI Codes:
 * "Company Register Search" and its OpenCorporates API token). The form draws a marker
 * <span class="tssggs-depends" data-switch="<switch name>" data-fields="<field name>,..."></span>.
 */
// leading semicolon: the module joins every script into one bundle
;(function () {
    'use strict';

    function update() {
        document.querySelectorAll('.tssggs-depends[data-switch]').forEach(function (marker) {
            var form   = marker.closest('form') || document;
            var toggle = form.querySelector('input[type="checkbox"][name="' + marker.getAttribute('data-switch') + '"]');
            if (!toggle) {
                return;
            }
            marker.getAttribute('data-fields').split(',').forEach(function (name) {
                var field = form.querySelector('[name="' + name.trim() + '"]');
                var group = field ? field.closest('.lu-form-group') || field.parentElement : null;
                if (group) {
                    group.style.display = toggle.checked ? '' : 'none';
                }
            });
        });
    }

    // the switch is a Vue component: its checkbox follows the Yes / No choice a moment after the click
    function soon() {
        setTimeout(update, 60);
        setTimeout(update, 250);
    }

    document.addEventListener('change', update, true);
    document.addEventListener('click', soon, true);
    document.addEventListener('keyup', soon, true);
    new MutationObserver(update).observe(document.documentElement, {childList: true, subtree: true});
    update();
})();
