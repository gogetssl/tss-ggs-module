{* The end of a dialog opened with dialog_start.tpl: the buttons ($submit: text of the green one) *}
                </form>
            </div>
            <div class="mod-modal-foot">
                <button type="submit" form="tssggs-dialog-form" class="mod-btn mod-btn--success"><span class="mod-btn-text">{if $submit|default:'' !== ''}{$submit nofilter}{else}{t key='admin.kernel.submit'}{/if}</span></button>
                <button type="button" class="mod-btn mod-btn--danger mod-btn--outline mod-btn--plain" data-dialog-close><span class="mod-btn-text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
