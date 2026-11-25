{* One task of the Automation Queue ($row): the cells in the order of the columns, then the buttons *}
<td><div>{$row.id}</div></td>
<td><div><a href="clientsservices.php?userid={$row.client_id}&amp;productselect={$row.service_id}">#{$row.service_id}</a></div></td>
<td><div>{$row.domain}</div></td>
<td><div>{$row.type}</div></td>
<td><span class="mod-tag mod-tip mod-tag--{$row.tone} mod-tag--sm">{$row.label}</span></td>
<td><div>{$row.attempts}</div></td>
<td><div>{$row.error_message}</div></td>
<td><div>{$row.updated_at}</div></td>
<td class="mod-table-actions mod-solo">
    {if $row.canReset}
        <button type="button" class="mod-btn mod-btn--link mod-btn--plain mod-tip mod-btn--icon mod-btn--warning"
                aria-label="{t key='data_tables.queue.buttons.reset_task_button.resetTask'}" data-title="{t key='data_tables.queue.buttons.reset_task_button.resetTask'}"
                hx-post="{url action='reset' id=$row.id}" hx-target="#tssggs-queue" hx-swap="innerHTML"
                hx-confirm="{t key='data_tables.queue.modals.reset_task_modal.description'}"
                data-confirm-title="{t key='data_tables.queue.modals.reset_task_modal.title'}">
            <span class="mod-btn-icon tssggs-icobox">{icon name='restore'}</span>
        </button>
    {/if}
</td>
