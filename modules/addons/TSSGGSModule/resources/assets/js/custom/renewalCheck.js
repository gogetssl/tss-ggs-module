/**
 * Settings > SSL certificates: what the renewal invoice lead and payment term mean together, redrawn while the
 * administrator changes either field. The box is drawn by SslSettingsForm::renewalCheck() with every text already
 * translated in its data-text-* attributes; this script only puts the numbers in. Same rules as
 * App\Libs\RenewalPolicy::warning() and SslSettingsForm::renewalCheckBody().
 */
// the leading semicolon matters: the module joins every script into one bundle, and the file before this one may end
// without a semicolon - "{...}(function () {...})()" would then call the object and stop the whole bundle
;(function () {
    'use strict';

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function fill(text, vars) {
        return String(text || '').replace(/:lead|:term|:days|:safe/g, function (name) {
            return vars[name];
        });
    }

    function field(box, name) {
        var scope = box.closest('form') || document;
        var input = scope.querySelector('[name="' + name + '"]');
        return input ? parseInt(input.value, 10) : NaN;
    }

    function draw(box) {
        var lead = field(box, box.getAttribute('data-lead'));
        var term = field(box, box.getAttribute('data-term'));
        if (isNaN(lead) || isNaN(term)) {
            return;
        }
        var type  = box.getAttribute('data-type');
        var after = term - lead;
        var vars  = {
            ':lead': lead,
            ':term': term,
            ':days': Math.abs(after),
            ':safe': Math.max(0, type === 'subscription' ? lead - 2 : lead)
        };

        var summary = box.getAttribute(after < 0 ? 'data-text-before' : (after === 0 ? 'data-text-on' : 'data-text-after'));
        var html    = '<p class="tssggs-renewal-check__summary">' + escapeHtml(fill(summary, vars)) + '</p>';

        var level = '';
        if (type === 'subscription') {
            level = after >= 0 ? 'red' : (after === -1 ? 'amber' : '');
        } else if (after > 0) {
            level = 'amber';
        }
        if (level) {
            html += '<p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--' + level + '">'
                + escapeHtml(fill(box.getAttribute('data-text-' + level), vars)) + '</p>';
        }

        if (box.innerHTML !== html) {
            box.innerHTML = html;
        }
    }

    function drawAll() {
        var boxes = document.querySelectorAll('.tssggs-renewal-check');
        for (var i = 0; i < boxes.length; i++) {
            draw(boxes[i]);
        }
    }

    document.addEventListener('change', drawAll, true);
    document.addEventListener('input', drawAll, true);
})();
