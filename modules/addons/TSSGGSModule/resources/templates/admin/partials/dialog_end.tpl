{* The end of a dialog opened with dialog_start.tpl: the buttons ($submit: text of the green one) *}
                </form>
            </div>
            <div class="lu-modal__actions">
                <button type="submit" form="tssggs-dialog-form" class="lu-btn lu-btn--success"><span class="lu-btn__text">{if $submit|default:'' !== ''}{$submit nofilter}{else}{t key='admin.kernel.submit'}{/if}</span></button>
                <button type="button" class="lu-btn lu-btn--danger lu-btn--outline lu-btn--plain" data-dialog-close><span class="lu-btn__text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
