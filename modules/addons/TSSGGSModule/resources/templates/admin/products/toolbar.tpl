{* Button on the right of the products search: reads the vendor's product list again *}
<button type="button" class="lu-btn lu-btn--primary" hx-post="{url action='resync'}" hx-target="#tssggs-products" hx-swap="innerHTML"
        hx-confirm="{t key='index.data_tables.products.modals.re_sync_modal.description'}" data-confirm-title="{t key='index.data_tables.products.modals.re_sync_modal.reSync'}">
    <span class="lu-btn__text">{t key='index.data_tables.products.array_data_provider.data_table.reSync'}</span>
</button>
