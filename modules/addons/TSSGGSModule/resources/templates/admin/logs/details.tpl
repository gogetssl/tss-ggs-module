{*
    "Details" dialog of a log entry: the entry, its service and client, the API request and answer, the other stored
    data and the entries of the same service around that time, as coloured JSON ($json: HTML from
    App\Libs\Logs\LogBook::highlight). $missing: the entry was deleted meanwhile.
*}
<div class="mod-modal show mod-modal--zoom mod-modal--lg" data-dialog>
    <div class="mod-modal-frame">
        <div class="mod-modal-sheet">
            <div class="mod-modal-head mod-bar">
                <div class="mod-bar-title mod-text-md"><span class="mod-text-dim mod-text-regular">{t key='detailsTitle'}</span></div>
                <div class="mod-bar-tools">
                    <button type="button" class="mod-btn mod-btn--xs mod-btn--default mod-btn--icon mod-btn--link mod-btn--plain mod-modal-close" aria-label="{t key='admin.kernel.cancel'}" data-dialog-close>
                        <span class="mod-btn-icon tssggs-icobox">{icon name='close'}</span>
                    </button>
                </div>
            </div>
            <div class="mod-modal-body mod-solo">
                {if $missing}
                    <div class="tssggs-note is-warn">{t key='detailsMissing'}</div>
                {else}
                    <div class="mod-field mod-w-full">
                        <label class="mod-field-label"><span>{t key='detailsData'}</span></label>
                        <pre class="mod-code tssggs-json">{$json nofilter}</pre>
                    </div>
                {/if}
            </div>
            <div class="mod-modal-foot">
                <button type="button" class="mod-btn mod-btn--danger mod-btn--outline mod-btn--plain" data-dialog-close><span class="mod-btn-text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
