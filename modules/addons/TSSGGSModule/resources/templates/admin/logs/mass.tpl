{* What can be done with the ticked entries: Delete (the ticked boxes go along) *}
<button type="button" class="lu-btn lu-btn--danger lu-btn--outline" hx-post="{url action='deleteMany'}" hx-include="[data-row-check]:checked" hx-target="#tssggs-logs" hx-swap="innerHTML"
        hx-confirm="{t key='deleteManyConfirm'}" data-confirm-title="{t key='deleteManyTitle'}">
    <span class="lu-btn__icon tssggs-icobox">{icon name='delete'}</span><span class="lu-btn__text">{t key='delete'}</span>
</button>
