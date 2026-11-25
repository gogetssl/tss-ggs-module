{*
    Pricing page: toolbar (currency, margin, CSV), then the grid of prices. Every form posts and comes back to this
    page. Styles: theme.css (.tssggs-pricing__*).
*}
<span class="tssggs-html tssggs-notes">
    {if $error}<div class="tssggs-note is-bad">{$error}</div>{/if}
    {foreach $notices as $notice}
        <div class="tssggs-note is-ok">{$notice}</div>
    {/foreach}
    {if $markupNote}
        <div class="tssggs-note">{$markupNote} <a href="{url page='markups'}">{t key='markup_edit'}</a></div>
    {/if}
    {if $showHelp}<div class="tssggs-note">{t key='grid_help'}</div>{/if}
</span>

<span class="tssggs-html">
    {if $preview}
        {include file='admin/pricing/preview.tpl'}
    {/if}

    <div class="tssggs-toolbar" x-data>
        <form method="get" action="addonmodules.php">
            <input type="hidden" name="module" value="{$moduleName}">
            <input type="hidden" name="mod-page" value="pricing">
            <label>{t key='currency'}</label>
            <select name="currency" class="form-control" @change="$el.form.submit()">
                {foreach $currencies as $currency}
                    <option value="{$currency.id}"{if $currency.id === $currencyId} selected{/if}>{$currency.code}</option>
                {/foreach}
            </select>
        </form>

        <form method="post" action="{url action='applyMargin'}" class="tssggs-toolbar__group" data-confirm="{t key='confirm_margin'}">
            <input type="hidden" name="token" value="{$token}">
            <input type="hidden" name="currency" value="{$currencyId}">
            <div>
                <label>{t key='margin'}</label>
                <input class="form-control" name="margin" style="width:90px" required placeholder="20">
            </div>
            <select name="scope" class="form-control" style="width:auto">
                <option value="currency">{t key='scope_currency'}</option>
                <option value="all">{t key='scope_all'}</option>
            </select>
            <button class="mod-btn mod-btn--primary" type="submit">{t key='apply_margin'}</button>
        </form>

        <span class="tssggs-toolbar__space"></span>

        <a class="mod-btn mod-btn--default" href="{url action='export' token=$token}">{t key='export'}</a>
        <form method="post" enctype="multipart/form-data" action="{url action='importPreview'}" class="tssggs-toolbar__group is-centered">
            <input type="hidden" name="token" value="{$token}">
            <input type="hidden" name="currency" value="{$currencyId}">
            <input type="file" name="csv" accept=".csv,text/csv" required style="max-width:220px">
            <button class="mod-btn mod-btn--default" type="submit">{t key='import'}</button>
        </form>
    </div>

    {if !$rows}
        <p>{t key='no_products'}</p>
    {else}
        {* only the changed fields are posted (a large catalogue would exceed max_input_vars).
           No ">" inside a <form> tag: WHMCS adds its token field after the first ">" it finds. *}
        <form method="post" action="{url action='save'}" x-data
              @submit="$el.querySelectorAll('input[name^=price]').forEach(function (i) { i.disabled = i.value === i.defaultValue })">
            <input type="hidden" name="token" value="{$token}">
            <input type="hidden" name="currency" value="{$currencyId}">
            <table class="table table-condensed tssggs-pricing__grid">
                <thead>
                    <tr>
                        <th>{t key='product'}</th>
                        <th style="width:70px">{t key='auto'}</th>
                        {foreach $terms as $term}
                            <th style="width:190px">{t key="term_$term"}</th>
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {foreach $rows as $row}
                        <tr{if $row.isBase} class="is-base"{/if}>
                            <td>
                                {if $row.isBase}
                                    <strong>{$row.name}</strong>
                                    <small class="tssggs-muted">#{$row.id} · {$row.vendor}{if $row.missing} · {t key='not_in_catalogue'}{/if}</small>
                                {else}
                                    <span class="tssggs-pricing__sub">+ {t key="item_{$row.item}"}</span>
                                {/if}
                            </td>
                            <td>
                                {if $row.isBase}
                                    <input type="checkbox" value="1"{if $row.auto} checked{/if} title="{t key='auto_title'}"
                                           @change="$el.nextElementSibling.disabled = false; $el.nextElementSibling.value = $el.checked ? 1 : 0">
                                    <input type="hidden" name="auto[{$row.id}]" value="" disabled>
                                {/if}
                            </td>
                            {foreach $row.cells as $cell}
                                {if $cell.none}
                                    <td class="tssggs-muted">-</td>
                                {else}
                                    <td>
                                        <input class="form-control input-sm tssggs-pricing__price{if $cell.below} is-below{/if}"
                                               name="price[{$row.id}][{$row.item}][{$cell.term}]" value="{$cell.price}" placeholder="{t key='disabled'}">
                                        <br><small class="tssggs-pricing__cost{if $cell.below} is-below{/if}">
                                            {if $cell.cost === ''}{t key='no_cost'}{else}{t key='cost' cost=$cell.cost}{if $cell.margin !== ''} · {$cell.margin} %{/if}{/if}{if $cell.below} · {t key='below_cost'}{/if}
                                        </small>
                                        {if $cell.clientPrice !== ''}
                                            <br><small class="tssggs-pricing__client" title="{t key='client_price_title'}">{t key='client_price' price=$cell.clientPrice rate=$cell.clientRate}</small>
                                        {/if}
                                    </td>
                                {/if}
                            {/foreach}
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            <button class="mod-btn mod-btn--success" type="submit">{t key='save_all'}</button>
        </form>
    {/if}
</span>
