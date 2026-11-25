{*
    One entry of the log ($row from App\Libs\Logs\LogBook::present): the cells in the order of the columns, then
    Show Details (only for an entry that has details) and Delete.
*}
<td>
    <div class="mod-line">
        <div class="mod-check">
            <label>
                <input class="mod-check-input table-mass-action-check" type="checkbox" name="ids[]" value="{$row.id}" data-row-check>
                <span class="mod-check-box"></span>
            </label>
        </div>
        <span>{$row.id}</span>
    </div>
</td>
<td><div>{if $row.related}<a href="{$row.related.url}">{$row.related.text}</a>{else}-{/if}</div></td>
<td><div>{$row.message}</div></td>
<td><span class="mod-tag mod-tip mod-tag--{$row.tone} mod-tag--status mod-tag--sm">{$row.label}</span></td>
<td><div>{$row.date}</div></td>
<td class="mod-table-actions">
    <button type="button" class="mod-btn mod-btn--link mod-btn--plain mod-tip mod-btn--default mod-btn--sm mod-btn--icon{if !$row.hasDetails} disabled{/if}"
            aria-label="{t key='showDetails'}" data-title="{t key='showDetails'}"{if !$row.hasDetails} disabled{/if}
            hx-get="{url action='details' id=$row.id}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
        <span class="mod-btn-icon tssggs-icobox">{icon name='information'}</span>
    </button>
    <button type="button" class="mod-btn mod-btn--link mod-btn--plain mod-tip mod-btn--danger mod-btn--sm mod-btn--icon"
            aria-label="{t key='delete'}" data-title="{t key='delete'}"
            hx-post="{url action='delete' id=$row.id}" hx-target="#tssggs-logs" hx-swap="innerHTML"
            hx-confirm="{t key='deleteConfirm'}" data-confirm-title="{t key='deleteTitle'}">
        <span class="mod-btn-icon tssggs-icobox">{icon name='delete'}</span>
    </button>
</td>
