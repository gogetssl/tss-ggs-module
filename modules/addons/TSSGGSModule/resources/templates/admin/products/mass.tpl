{* What can be done with the ticked products: Import opens the import dialog for them (the ticked boxes go along) *}
<button type="button" class="mod-btn mod-btn--success" hx-get="{url action='import'}" hx-include="[data-row-check]:checked" hx-target="#tssggs-dialog" hx-swap="innerHTML">
    <span class="mod-btn-icon tssggs-icobox">{icon name='import'}</span><span class="mod-btn-text">{t key='index.data_tables.products.array_data_provider.data_table.import'}</span>
</button>
