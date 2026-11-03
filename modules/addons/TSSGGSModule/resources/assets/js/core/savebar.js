/**
 * Sticky Save bar of the settings screens (.tssggs-savebar, built by Settings\Index\Concerns\SettingsLayout).
 * One bar saves the whole screen: any change in the form enables "Save changes" and switches the status to
 * "Unsaved changes"; a successful save clears it; leaving the page with unsaved changes asks first.
 */
;(function () {
    var dirty = false;

    function bars() {
        return document.querySelectorAll('.tssggs-savebar');
    }

    function status(bar) {
        return bar.querySelector('.tssggs-savebar__status');
    }

    function saveButton(bar) {
        return bar.querySelector('.lu-btn--success');
    }

    function render(bar, state) {
        var st = status(bar), btn = saveButton(bar);
        if (!st || st.getAttribute('data-always')) {
            return;
        }
        // state: clean | dirty | failed
        st.textContent = st.getAttribute(state === 'dirty' ? 'data-dirty' : state === 'failed' ? 'data-failed' : 'data-clean');
        st.className = 'tssggs-savebar__status' + (state === 'clean' ? '' : ' is-' + state);
        bar.classList.toggle('is-dirty', state !== 'clean');
        if (btn) {
            btn.disabled = state === 'clean';
        }
    }

    function setState(state) {
        dirty = state !== 'clean';
        bars().forEach(function (bar) { render(bar, state); });
    }

    // buttons are rendered by Vue after the bar: (re)apply the initial state to every bar we have not seen yet
    function init() {
        bars().forEach(function (bar) {
            if (!bar.getAttribute('data-sb')) {
                bar.setAttribute('data-sb', '1');
                render(bar, dirty ? 'dirty' : 'clean');
            }
        });
    }

    function inScreen(target) {
        return target && target.closest && target.closest('form') && target.closest('form').querySelector('.tssggs-savebar');
    }

    ['input', 'change'].forEach(function (type) {
        document.addEventListener(type, function (e) {
            if (inScreen(e.target) && !(e.target.closest && e.target.closest('.tssggs-savebar'))) {
                setState('dirty');
            }
        }, true);
    });

    document.addEventListener('tssggs-form-result', function (e) {
        if (!bars().length || (e.detail && e.detail.custom)) {
            return;
        }
        setState(e.detail && e.detail.ok ? 'clean' : 'failed');
        if (e.detail && !e.detail.ok) {
            bars().forEach(function (bar) { var b = saveButton(bar); if (b) { b.disabled = false; } });
        }
    });

    // leaving with unsaved changes: ask first (links), and the browser's own warning for tab close / reload
    document.addEventListener('click', function (e) {
        var a = e.target.closest && e.target.closest('a[href]');
        if (!dirty || !a || a.target === '_blank' || /^(#|javascript:)/.test(a.getAttribute('href'))) {
            return;
        }
        var st = bars().length ? status(bars()[0]) : null;
        if (!window.confirm(st ? st.getAttribute('data-confirm') : 'Unsaved changes. Leave this page?')) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            dirty = false;
        }
    }, true);

    window.addEventListener('beforeunload', function (e) {
        if (dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    new MutationObserver(init).observe(document.documentElement, {childList: true, subtree: true});
    init();
})();
