{*
    Wizard page 5 "Choose Products": the list of the Products page, loaded into this page. Its buttons (Import,
    Re-sync, the filters) belong to the Products page and put the list back into #tssggs-products.
*}
{include file='admin/partials/alert.tpl' text={t key='step5.productsInfo'}}
{include file='admin/partials/lazy.tpl' url={url page='products'} id='tssggs-products' swap='innerHTML'}
<a class="mod-btn mod-btn--success mt30_custom" href="{$next}"><span class="mod-btn-text">{t key='step5.next_step_btn'}</span></a>
