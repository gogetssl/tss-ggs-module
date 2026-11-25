{* What can be done with the ticked entries: Delete (the ticked boxes go along) *}
<button type="button" class="mod-btn mod-btn--danger mod-btn--outline" hx-post="{url action='deleteMany'}" hx-include="[data-row-check]:checked" hx-target="#tssggs-logs" hx-swap="innerHTML"
        hx-confirm="{t key='deleteManyConfirm'}" data-confirm-title="{t key='deleteManyTitle'}">
    <span class="mod-btn-icon tssggs-icobox">{icon name='delete'}</span><span class="mod-btn-text">{t key='delete'}</span>
</button>
