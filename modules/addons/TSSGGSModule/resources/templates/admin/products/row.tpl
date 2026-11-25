{*
    One product of the list ($row). A product that is not imported yet can be ticked (for Import); an imported one
    has the Manage links and the edit button. The links post their action and put the list back.
*}
<td>
    <div class="mod-line">
        <div class="mod-check">
            <label>
                <input class="mod-check-input table-mass-action-check" type="checkbox" name="ids[]" value="{$row.id}" data-row-check{if $row.imported} disabled{/if}>
                <span class="mod-check-box"></span>
            </label>
        </div>
        <span>{$row.id}</span>
    </div>
</td>
<td><div>{$row.productName}</div></td>
<td><div>{$row.brand}</div></td>
<td><div>{$row.validation}</div></td>
<td><div>{$row.provider}</div></td>
<td><div>{$row.billing}</div></td>
<td>
    <div>
        {foreach $row.badges as $badge}
            <span class="mod-tag mod-tag--sm mod-tag--{$badge.tone} tssggs-badge">{$badge.text}</span>
        {/foreach}
    </div>
</td>
<td>
    <div>
        <span class="tssggs-manage">
            {foreach $row.actions as $action}
                {if !$action@first} · {/if}<a href="#" hx-post="{url action=$action.action pid=$row.pid}" hx-target="#tssggs-products" hx-swap="innerHTML"
                   {if $action.confirm !== ''}hx-confirm="{$action.confirm}" data-confirm-title="{$action.text}"{/if}>{$action.text}</a>
            {/foreach}
            {foreach $row.addons as $addonId}
                · <a href="configaddons.php?action=manage&amp;id={$addonId}">{t key='index.data_tables.products.array_data_provider.data_table.product_addon' id=$addonId}</a>
            {/foreach}
        </span>
    </div>
</td>
<td class="mod-table-actions mod-solo">
    {if $row.imported}
        <button type="button" class="mod-btn mod-btn--link mod-btn--plain mod-tip mod-btn--icon" aria-label="{t key='index.data_tables.products.modals.configuration_modal.title'}"
                data-title="{t key='index.data_tables.products.modals.configuration_modal.title'}"
                hx-get="{url action='edit' id=$row.id}" hx-target="#tssggs-dialog" hx-swap="innerHTML" hx-params="none">
            <span class="mod-btn-icon tssggs-icobox">{icon name='pencil'}</span>
        </button>
    {/if}
</td>
