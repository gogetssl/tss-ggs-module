{* The markup rules: the part of the Markups page that htmx reloads *}
{include file='admin/partials/flash.tpl'}

<table class="table table-striped">
    <thead>
        <tr>
            <th>{t key='client'}</th>
            <th>{t key='product'}</th>
            <th style="width:130px">{t key='markup'}</th>
            <th style="width:150px">{t key='updated'}</th>
            <th style="width:90px"></th>
        </tr>
    </thead>
    <tbody>
        {foreach $rules as $rule}
            <tr>
                <td>
                    {if $rule.clientId}
                        <a href="clientssummary.php?userid={$rule.clientId}">#{$rule.clientId} {$rule.client}</a>
                    {else}
                        <em>{t key='all_clients'}</em>
                    {/if}
                </td>
                <td>{if $rule.productId}{$rule.product}{else}<em>{t key='all_products'}</em>{/if}</td>
                <td>{$rule.markup} %</td>
                <td>{$rule.updated}</td>
                <td>
                    <a href="#" hx-post="{url action='delete' id=$rule.id}" hx-target="#tssggs-markups-list"
                       hx-confirm="{t key='confirm_delete'}">{t key='delete'}</a>
                </td>
            </tr>
        {foreachelse}
            <tr><td colspan="5" style="text-align:center">{t key='empty'}</td></tr>
        {/foreach}
    </tbody>
</table>
