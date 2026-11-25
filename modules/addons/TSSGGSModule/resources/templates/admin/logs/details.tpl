{*
    "Details" dialog of a log entry: the entry, its service and client, the API request and answer, the other stored
    data and the entries of the same service around that time, as coloured JSON ($json: HTML from
    App\Libs\Logs\LogBook::highlight). $missing: the entry was deleted meanwhile.
*}
<div class="lu-modal show modal--zoomIn lu-modal--lg" data-dialog>
    <div class="lu-modal__dialog">
        <div class="lu-modal__content">
            <div class="lu-modal__top lu-top">
                <div class="lu-top__title lu-type-6"><span class="lu-text-faded lu-font-weight-normal">{t key='detailsTitle'}</span></div>
                <div class="lu-top__toolbar">
                    <button type="button" class="lu-btn lu-btn--xs lu-btn--default lu-btn--icon lu-btn--link lu-btn--plain closeModal" aria-label="{t key='admin.kernel.cancel'}" data-dialog-close>
                        <span class="lu-btn__icon tssggs-icobox">{icon name='close'}</span>
                    </button>
                </div>
            </div>
            <div class="lu-modal__body lu-single-element">
                {if $missing}
                    <div class="tssggs-note is-warn">{t key='detailsMissing'}</div>
                {else}
                    <div class="lu-form-group lu-w-100">
                        <label class="lu-form-label"><span>{t key='detailsData'}</span></label>
                        <pre class="pretty-print tssggs-json">{$json nofilter}</pre>
                    </div>
                {/if}
            </div>
            <div class="lu-modal__actions">
                <button type="button" class="lu-btn lu-btn--danger lu-btn--outline lu-btn--plain" data-dialog-close><span class="lu-btn__text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
