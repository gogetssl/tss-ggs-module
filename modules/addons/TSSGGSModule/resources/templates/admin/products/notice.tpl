{* A dialog that only says something ($text), e.g. "tick a product first" *}
<div class="lu-modal show modal--zoomIn" data-dialog>
    <div class="lu-modal__dialog">
        <div class="lu-modal__content">
            <div class="lu-modal__body lu-single-element"><div class="tssggs-note is-warn">{$text}</div></div>
            <div class="lu-modal__actions">
                <button type="button" class="lu-btn lu-btn--danger lu-btn--outline lu-btn--plain" data-dialog-close><span class="lu-btn__text">{t key='admin.kernel.cancel'}</span></button>
            </div>
        </div>
    </div>
</div>
