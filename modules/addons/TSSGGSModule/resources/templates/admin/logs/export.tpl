{*
    "Export CSV" dialog: the period ($from, $to: Y-m-d, the oldest and the newest entry) and the types
    ($types: type => name; none chosen = every type). The form is a plain GET that answers with the file, so the
    page stays; the dialog closes a moment after the click.
*}
<div class="lu-modal show modal--zoomIn" data-dialog>
    <div class="lu-modal__dialog">
        <div class="lu-modal__content">
            <div class="lu-modal__top lu-top">
                <div class="lu-top__title lu-type-6"><span class="lu-text-faded lu-font-weight-normal">{t key='exportCsv'}</span></div>
                <div class="lu-top__toolbar">
                    <button type="button" class="lu-btn lu-btn--xs lu-btn--default lu-btn--icon lu-btn--link lu-btn--plain closeModal" aria-label="{t key='admin.kernel.cancel'}" data-dialog-close>
                        <span class="lu-btn__icon tssggs-icobox">{icon name='close'}</span>
                    </button>
                </div>
            </div>
            <div class="lu-modal__body lu-single-element">
                <form id="tssggs-export-form" method="get" action="addonmodules.php" class="lu-w-100" x-data
                      @submit="setTimeout(function () { var close = document.querySelector('#tssggs-dialog [data-dialog-close]'); if (close) { close.click() } }, 400)">
                    <input type="hidden" name="module" value="{$moduleName}">
                    <input type="hidden" name="mod-page" value="logs">
                    <input type="hidden" name="mod-action" value="download">
                    <div class="lu-form-group">
                        <label class="lu-form-label lu-single-element"><span>{t key='exportFrom'}</span>{tip key='exportFrom_description'}</label>
                        <input type="date" name="from" class="lu-form-control" value="{$from}">
                    </div>
                    <div class="lu-form-group">
                        <label class="lu-form-label lu-single-element"><span>{t key='exportTo'}</span>{tip key='exportTo_description'}</label>
                        <input type="date" name="to" class="lu-form-control" value="{$to}">
                    </div>
                    <div class="lu-form-group">
                        <label class="lu-form-label lu-single-element"><span>{t key='exportTypes'}</span>{tip key='exportTypes_description'}</label>
                        <select name="types[]" class="lu-form-control" multiple data-placeholder="{t key='exportAllTypes'}">
                            {foreach $types as $value => $name}
                                <option value="{$value}">{$name}</option>
                            {/foreach}
                        </select>
                    </div>
                </form>
            </div>
            <div class="lu-modal__actions">
                <button type="submit" form="tssggs-export-form" class="lu-btn lu-btn--success"><span class="lu-btn__text">{t key='exportConfirm'}</span></button>
                <button type="button" class="lu-btn lu-btn--danger lu-btn--outline lu-btn--plain" data-dialog-close><span class="lu-btn__text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
