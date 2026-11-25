{*
    "Export CSV" dialog: the period ($from, $to: Y-m-d, the oldest and the newest entry) and the types
    ($types: type => name; none chosen = every type). The form is a plain GET that answers with the file, so the
    page stays; the dialog closes a moment after the click.
*}
<div class="mod-modal show mod-modal--zoom" data-dialog>
    <div class="mod-modal-frame">
        <div class="mod-modal-sheet">
            <div class="mod-modal-head mod-bar">
                <div class="mod-bar-title mod-text-md"><span class="mod-text-dim mod-text-regular">{t key='exportCsv'}</span></div>
                <div class="mod-bar-tools">
                    <button type="button" class="mod-btn mod-btn--xs mod-btn--default mod-btn--icon mod-btn--link mod-btn--plain mod-modal-close" aria-label="{t key='admin.kernel.cancel'}" data-dialog-close>
                        <span class="mod-btn-icon tssggs-icobox">{icon name='close'}</span>
                    </button>
                </div>
            </div>
            <div class="mod-modal-body mod-solo">
                <form id="tssggs-export-form" method="get" action="addonmodules.php" class="mod-w-full" x-data
                      @submit="setTimeout(function () { var close = document.querySelector('#tssggs-dialog [data-dialog-close]'); if (close) { close.click() } }, 400)">
                    <input type="hidden" name="module" value="{$moduleName}">
                    <input type="hidden" name="mod-page" value="logs">
                    <input type="hidden" name="mod-action" value="download">
                    <div class="mod-field">
                        <label class="mod-field-label mod-solo"><span>{t key='exportFrom'}</span>{tip key='exportFrom_description'}</label>
                        <input type="date" name="from" class="mod-input" value="{$from}">
                    </div>
                    <div class="mod-field">
                        <label class="mod-field-label mod-solo"><span>{t key='exportTo'}</span>{tip key='exportTo_description'}</label>
                        <input type="date" name="to" class="mod-input" value="{$to}">
                    </div>
                    <div class="mod-field">
                        <label class="mod-field-label mod-solo"><span>{t key='exportTypes'}</span>{tip key='exportTypes_description'}</label>
                        <select name="types[]" class="mod-input" multiple data-placeholder="{t key='exportAllTypes'}">
                            {foreach $types as $value => $name}
                                <option value="{$value}">{$name}</option>
                            {/foreach}
                        </select>
                    </div>
                </form>
            </div>
            <div class="mod-modal-foot">
                <button type="submit" form="tssggs-export-form" class="mod-btn mod-btn--success"><span class="mod-btn-text">{t key='exportConfirm'}</span></button>
                <button type="button" class="mod-btn mod-btn--danger mod-btn--outline mod-btn--plain" data-dialog-close><span class="mod-btn-text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
