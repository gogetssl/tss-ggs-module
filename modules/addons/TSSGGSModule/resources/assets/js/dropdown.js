/*
 * Dropdowns of the module's forms. Every <select class="lu-form-control"> of a page on the new kernel is shown as
 * the module's own dropdown (the look of the old framework: classes lu-selectize-*), with a search: type to
 * narrow the list. The <select> stays in the page, hidden, and keeps the value - forms, htmx and Alpine work with
 * it as with any select (a choice fires its "change" event).
 *
 * Nothing to do in a template: write a normal <select class="lu-form-control">.
 *   data-native          keeps the browser's own select
 *   multiple             is left alone
 * Keys: arrows move, Enter chooses, Escape closes.
 */
(function () {
    'use strict';

    var SELECTOR = '#layers2 select.lu-form-control:not(.selectized):not([multiple]):not([data-native])';
    var openOne = null;

    function element(tag, className) {
        var node = document.createElement(tag);
        node.className = className;
        return node;
    }

    function enhance(select) {
        select.classList.add('selectized');
        select.style.display = 'none';
        select.tabIndex = -1;

        var control = element('div', 'lu-selectize-control single');
        var box = element('div', 'lu-selectize-input lu-form-control items has-options');
        var item = element('div', 'item');
        var search = element('input', '');
        search.type = 'text';
        search.autocomplete = 'off';
        search.style.width = '4px';
        var dropdown = element('div', 'lu-selectize-dropdown single lu-form-control');
        dropdown.style.display = 'none';
        var list = element('div', 'lu-selectize-dropdown-content');
        dropdown.appendChild(list);
        box.appendChild(item);
        box.appendChild(search);
        control.appendChild(box);
        control.appendChild(dropdown);
        select.parentNode.insertBefore(control, select.nextSibling);

        var active = -1;       // index (in the shown list) of the highlighted option
        var shown = [];        // the <option> elements the list shows now

        function chosen() {
            return select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
        }

        // the closed box: the chosen option's text
        function render() {
            var option = chosen();
            var text = option ? option.textContent : '';
            item.textContent = text;
            item.style.display = text !== '' && search.value === '' ? '' : 'none';
            box.classList.toggle('has-items', text !== '');
            box.classList.toggle('full', text !== '');
            box.classList.toggle('not-full', text === '');
            box.classList.toggle('disabled', select.disabled);
            control.style.pointerEvents = select.disabled ? 'none' : '';
            search.disabled = select.disabled;
        }

        // the open list: the options that contain what was typed
        function fill() {
            var query = search.value.trim().toLowerCase();
            list.textContent = '';
            shown = [];
            Array.prototype.forEach.call(select.options, function (option) {
                // an option without a text is the "nothing chosen yet" placeholder: it is not offered
                if (option.disabled || option.textContent === '' || (query !== '' && option.textContent.toLowerCase().indexOf(query) === -1)) {
                    return;
                }
                var row = element('div', 'option' + (option.selected ? ' selected' : ''));
                row.setAttribute('data-selectable', '');
                row.textContent = option.textContent;
                list.appendChild(row);
                shown.push(option);
            });
            active = Math.max(0, shown.indexOf(chosen()));
            highlight();
        }

        function highlight() {
            Array.prototype.forEach.call(list.children, function (row, index) {
                row.classList.toggle('active', index === active);
            });
            if (list.children[active] && list.children[active].scrollIntoView) {
                list.children[active].scrollIntoView({ block: 'nearest' });
            }
        }

        function isOpen() {
            return dropdown.style.display !== 'none';
        }

        function open() {
            if (select.disabled || isOpen()) {
                return;
            }
            if (openOne) {
                openOne();
            }
            openOne = close;
            fill();
            dropdown.style.display = 'block';
            dropdown.style.visibility = 'visible';
            dropdown.style.width = box.offsetWidth + 'px';
            dropdown.style.top = box.offsetHeight + 'px';
            dropdown.style.left = '0px';
            box.classList.add('focus', 'input-active', 'dropdown-active');
            search.style.opacity = '1';
        }

        function close() {
            if (openOne === close) {
                openOne = null;
            }
            dropdown.style.display = 'none';
            box.classList.remove('focus', 'input-active', 'dropdown-active');
            search.value = '';
            search.style.width = '4px';
            render();
        }

        function choose(option) {
            if (option && select.value !== option.value) {
                select.value = option.value;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            close();
        }

        box.addEventListener('mousedown', function (event) {
            event.preventDefault();
            if (isOpen()) {
                close();
            } else {
                open();
                search.focus();
            }
        });
        search.addEventListener('focus', open);
        search.addEventListener('input', function () {
            search.style.width = (search.value.length + 1) + 'ch';
            item.style.display = search.value === '' && item.textContent !== '' ? '' : 'none';
            if (!isOpen()) {
                open();
            } else {
                fill();
            }
        });
        search.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (!isOpen()) {
                    open();
                    return;
                }
                active = Math.min(shown.length - 1, Math.max(0, active + (event.key === 'ArrowDown' ? 1 : -1)));
                highlight();
            } else if (event.key === 'Enter') {
                if (isOpen()) {
                    event.preventDefault();
                    choose(shown[active]);
                }
            } else if (event.key === 'Escape' || event.key === 'Tab') {
                if (isOpen()) {
                    close();
                }
            }
        });
        list.addEventListener('mousedown', function (event) {
            event.preventDefault();
            var row = event.target.closest('.option');
            if (row) {
                choose(shown[Array.prototype.indexOf.call(list.children, row)]);
            }
        });
        list.addEventListener('mousemove', function (event) {
            var row = event.target.closest('.option');
            if (row) {
                active = Array.prototype.indexOf.call(list.children, row);
                highlight();
            }
        });
        // the select changed from outside: another script chose a value, Alpine switched it off, options were replaced
        select.addEventListener('change', render);
        new MutationObserver(function () {
            render();
            if (isOpen()) {
                fill();
            }
        }).observe(select, { attributes: true, attributeFilter: ['disabled'], childList: true, subtree: true });

        // a required select without a value: the browser cannot point at a hidden field, so the dropdown opens
        select.addEventListener('invalid', function (event) {
            event.preventDefault();
            box.classList.add('lu-is-error');
            open();
            search.focus();
        });
        select.addEventListener('change', function () {
            box.classList.remove('lu-is-error');
        });

        render();
    }

    // a click anywhere else closes the open dropdown
    document.addEventListener('mousedown', function (event) {
        if (openOne && !(event.target.closest && event.target.closest('.lu-selectize-control'))) {
            openOne();
        }
    });

    function enhanceAll() {
        Array.prototype.forEach.call(document.querySelectorAll(SELECTOR), enhance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceAll);
    } else {
        enhanceAll();
    }
    document.addEventListener('htmx:afterSettle', enhanceAll);
})();
