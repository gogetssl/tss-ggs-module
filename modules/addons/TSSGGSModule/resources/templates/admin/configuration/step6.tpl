{*
    Wizard page 6 "Finalization": what is in place, what to do next, and the button that ends the wizard.
    $pending: "migrate" was chosen but no migration has completed yet. $products: "<b>N Products imported</b> ...".
    The texts with <b> are the module's own HTML.
*}
<span class="title_alert">{t key='step6.title_alert' html=true}</span>
{if $pending}
    <span class="api_alert"><div class="tssggs-note is-warn">{t key='step6.migration_pending' html=true}</div></span>
{/if}
<span class="api_alert">{t key='step6.step6_api' html=true}</span>
<span class="api_alert">{$products nofilter}</span>
<span class="title_list">{t key='step6.title_list'}</span>
<span class="item_list">{t key='step6.item_list1' html=true}</span>
<span class="item_list item_list2">{t key='step6.item_list2' html=true}</span>
<form method="post" action="{url action='finalize'}" class="mod-w-full">
    <input type="hidden" name="token" value="{$token}">
    <div class="mod-bar-tools">
        <button type="submit" class="mod-btn mod-btn--success"><span class="mod-btn-text">{t key='step6.finalize'}</span></button>
    </div>
</form>
