/*
 * Messages on the module's client area pages: $('#ModAlerts').alerts('success' | 'error', text) shows one below the
 * page heading, $('#ModAlerts').alerts('clear') removes them. The boxes are copies of the hidden ones in main.tpl
 * (data-prototype="success" / "error"). A message is shown as text: markup in it is never run.
 */
(function ($) {
    if (!$ || $.fn.alerts) {
        return;
    }

    var KEEP = 3;

    function plain(message) {
        // server texts arrive HTML-encoded ("&amp;"): decoded, then used as text only
        var doc = new DOMParser().parseFromString(String(message === undefined || message === null ? '' : message), 'text/html');
        return doc.body ? doc.body.textContent : '';
    }

    $.fn.alerts = function (type, message) {
        var box = this;
        if (type === 'clear') {
            box.children('[data-shown]').remove();
            return box;
        }
        var prototype = box.find('[data-prototype="' + type + '"]').first();
        if (!prototype.length) {
            return box;
        }
        var alert = prototype.clone();
        alert.removeAttr('data-prototype').removeAttr('hidden').attr('data-shown', '1');
        alert.find('strong').first().text(plain(message));
        alert.find('.close').on('click', function () {
            alert.remove();
        });
        box.append(alert.show());

        var shown = box.children('[data-shown]');
        if (shown.length > KEEP) {
            shown.slice(0, shown.length - KEEP).remove();
        }
        return box;
    };
})(window.jQuery);
