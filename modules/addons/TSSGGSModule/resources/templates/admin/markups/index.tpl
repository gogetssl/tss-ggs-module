{* Markups page: the form adds or changes a rule, the list below is reloaded by htmx after every change *}
{if $showIntro}
    {include file='admin/partials/alert.tpl' text={t key='intro'}}
{/if}

<span class="tssggs-html">
    <form method="post" action="{url action='save'}" class="tssggs-inline-form"
          hx-post="{url action='save'}" hx-target="#tssggs-markups-list">
        <input type="hidden" name="token" value="{$token}">
        <div>
            <label>{t key='client'}</label>
            <select name="client_id" class="form-control" style="width:260px">
                <option value="0">{t key='all_clients'}</option>
                {foreach $clients as $client}
                    <option value="{$client.id}">#{$client.id} {$client.name}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <label>{t key='product'}</label>
            <select name="product_id" class="form-control" style="width:300px">
                <option value="0">{t key='all_products'}</option>
                {foreach $products as $product}
                    <option value="{$product.id}">{$product.name}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <label>{t key='markup'}</label>
            <input name="markup" class="form-control" style="width:110px" placeholder="10 or -5" required>
        </div>
        <button class="lu-btn lu-btn--success" type="submit">{t key='save'}</button>
    </form>

    <div id="tssggs-markups-list">
        {include file='admin/markups/list.tpl'}
    </div>
</span>
