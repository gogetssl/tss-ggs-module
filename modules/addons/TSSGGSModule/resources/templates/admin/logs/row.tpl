{*
    One entry of the log ($row from App\Libs\Logs\LogBook::present): the cells in the order of the columns, then
    Show Details (only for an entry that has details) and Delete.
*}
<td>
    <div class="lu-rail">
        <div class="lu-form-check">
            <label>
                <input class="lu-form-checkbox table-mass-action-check" type="checkbox" name="ids[]" value="{$row.id}" data-row-check>
                <span class="lu-form-indicator"></span>
            </label>
        </div>
        <span>{$row.id}</span>
    </div>
</td>
<td><div>{if $row.related}<a href="{$row.related.url}">{$row.related.text}</a>{else}-{/if}</div></td>
<td><div>{$row.message}</div></td>
<td><span class="lu-label lu-tooltip lu-label--{$row.tone} lu-label--status lu-label--sm">{$row.label}</span></td>
<td><div>{$row.date}</div></td>
<td class="lu-cell-actions">
    <button type="button" class="lu-btn lu-btn--link lu-btn--plain lu-tooltip lu-btn--default lu-btn--sm lu-btn--icon{if !$row.hasDetails} disabled{/if}"
            aria-label="{t key='showDetails'}" data-title="{t key='showDetails'}"{if !$row.hasDetails} disabled{/if}
            hx-get="{url action='details' id=$row.id}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
        <span class="lu-btn__icon tssggs-icobox">{icon name='information'}</span>
    </button>
    <button type="button" class="lu-btn lu-btn--link lu-btn--plain lu-tooltip lu-btn--danger lu-btn--sm lu-btn--icon"
            aria-label="{t key='delete'}" data-title="{t key='delete'}"
            hx-post="{url action='delete' id=$row.id}" hx-target="#tssggs-logs" hx-swap="innerHTML"
            hx-confirm="{t key='deleteConfirm'}" data-confirm-title="{t key='deleteTitle'}">
        <span class="lu-btn__icon tssggs-icobox">{icon name='delete'}</span>
    </button>
</td>
