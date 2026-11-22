/**
 * Settings > API: the note above Save / Test Connection ("both accounts") is shown only while the other vendor's
 * Connect switch is on - with it off the buttons work on one account. The note is drawn by ApiSettingsForm with
 * data-switch = the switch's field name and starts hidden when the vendor is not connected.
 */
// the leading semicolon matters: the module joins every script into one bundle (see renewalCheck.js)
;(function () {
    'use strict';

    function update() {
        var boxes = document.querySelectorAll('.tssggs-api-scope[data-switch]');
        for (var i = 0; i < boxes.length; i++) {
            var box   = boxes[i];
            var scope = box.closest('form') || document;
            var input = scope.querySelector('input[type="checkbox"][name="' + box.getAttribute('data-switch') + '"]');
            if (input) {
                box.style.display = input.checked ? '' : 'none';
            }
        }
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
