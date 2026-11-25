{* A dialog that only says something ($text), e.g. "tick a product first" *}
<div class="mod-modal show mod-modal--zoom" data-dialog>
    <div class="mod-modal-frame">
        <div class="mod-modal-sheet">
            <div class="mod-modal-body mod-solo"><div class="tssggs-note is-warn">{$text}</div></div>
            <div class="mod-modal-foot">
                <button type="button" class="mod-btn mod-btn--danger mod-btn--outline mod-btn--plain" data-dialog-close><span class="mod-btn-text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
