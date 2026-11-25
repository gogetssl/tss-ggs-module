{* What a CSV import would change ($preview: accepted, rejected, unchanged); nothing is saved until "Import" *}
<div class="tssggs-note tssggs-pricing__preview">
    <h4>{t key='preview_title'}</h4>
    <p>{t key='preview_summary' accepted=$preview.accepted|@count rejected=$preview.rejected|@count unchanged=$preview.unchanged}</p>

    {if $preview.accepted}
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>{t key='product'}</th><th>{t key='item'}</th><th>{t key='term'}</th><th>{t key='currency'}</th>
                    <th>{t key='old_price'}</th><th>{t key='new_price'}</th><th>{t key='reseller_cost'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $preview.accepted as $row}
                    <tr{if $row.below} class="tssggs-row-bad"{/if}>
                        <td>{$row.product}</td>
                        <td>{t key="item_{$row.item}"}</td>
                        <td>{$row.term}</td>
                        <td>{$row.currency}</td>
                        <td>{if $row.old === ''}{t key='disabled'}{else}{$row.old}{/if}</td>
                        <td><strong>{if $row.new === ''}{t key='disabled'}{else}{$row.new}{/if}</strong>{if $row.below} <span class="tssggs-text-bad">{t key='below_cost'}</span>{/if}</td>
                        <td>{$row.cost}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}

    {if $preview.rejected}
        <h5>{t key='rejected_title'}</h5>
        <table class="table table-condensed">
            <thead><tr><th>{t key='line'}</th><th>{t key='data'}</th><th>{t key='reason'}</th></tr></thead>
            <tbody>
                {foreach $preview.rejected as $row}
                    <tr><td>{$row.line}</td><td><code>{$row.data}</code></td><td class="tssggs-text-bad">{$row.reason}</td></tr>
                {/foreach}
            </tbody>
        </table>
    {/if}

    <form method="post" action="{url action='importConfirm'}" class="tssggs-inline">
        <input type="hidden" name="token" value="{$token}">
        <button class="mod-btn mod-btn--success" type="submit"{if !$preview.accepted} disabled{/if}>{t key='import_confirm' count=$preview.accepted|@count}</button>
    </form>
    <form method="post" action="{url action='importCancel'}" class="tssggs-inline">
        <input type="hidden" name="token" value="{$token}">
        <button class="mod-btn mod-btn--default" type="submit">{t key='import_cancel'}</button>
    </form>
</div>
