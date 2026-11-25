/*
 * Emails page (templates/admin/emails/index.tpl): the message is edited with the TinyMCE that ships with WHMCS
 * (assets/js/tinymce), like on WHMCS's own e-mail template page.
 *
 *   [data-em-message]      the textarea
 *   [data-em-rich]         checkbox: rich editor on / off
 *   [data-em-plain]        checkbox "plain text e-mail": turns the rich editor off
 *   [data-em-insert]       merge-field chip: inserts its value at the cursor
 *   [data-em-rich-failed]  note shown when TinyMCE could not be loaded
 */
(function () {
    'use strict';

    var area = document.querySelector('[data-em-message]');
    var rich = document.querySelector('[data-em-rich]');
    if (!area || !rich) {
        return;
    }

    function editor() {
        return window.tinymce ? window.tinymce.get(area.id) : null;
    }

    function setRich(on) {
        if (!window.tinymce) {
            return;
        }
        if (on && !editor()) {
            window.tinymce.init({
                selector: '#' + area.id,
                height: 420,
                menubar: false,
                branding: false,
                plugins: 'link lists code table autolink',
                toolbar: 'undo redo | styleselect bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
                relative_urls: false,
                convert_urls: false,
                remove_script_host: false,
                verify_html: false,
                entity_encoding: 'raw'
            });
        } else if (!on && editor()) {
            editor().remove();
        }
    }

    function insert(text) {
        if (editor()) {
            editor().insertContent(text);
            return;
        }
        var start = area.selectionStart;
        area.value = area.value.slice(0, start) + text + area.value.slice(area.selectionEnd);
        area.focus();
    }

    rich.addEventListener('change', function () {
        setRich(rich.checked);
    });

    var plain = document.querySelector('[data-em-plain]');
    if (plain) {
        plain.addEventListener('change', function () {
            rich.checked = !plain.checked;
            setRich(rich.checked);
        });
    }

    document.addEventListener('click', function (event) {
        var chip = event.target.closest('[data-em-insert]');
        if (chip) {
            event.preventDefault();
            insert(chip.getAttribute('data-em-insert'));
        }
    });

    // the editor writes its text back into the textarea before the form is sent
    var form = document.querySelector('[data-em-form]');
    if (form) {
        form.addEventListener('submit', function () {
            if (window.tinymce) {
                window.tinymce.triggerSave();
            }
        });
    }

    var script = document.createElement('script');
    script.src = '../assets/js/tinymce/tinymce.min.js';
    script.onload = function () {
        setRich(rich.checked);
    };
    script.onerror = function () {
        rich.checked = false;
        var note = document.querySelector('[data-em-rich-failed]');
        if (note) {
            note.hidden = false;
        }
    };
    document.head.appendChild(script);
})();
