{* One task of the Automation Queue ($row): the cells in the order of the columns, then the buttons *}
<td><div>{$row.id}</div></td>
<td><div><a href="clientsservices.php?userid={$row.client_id}&amp;productselect={$row.service_id}">#{$row.service_id}</a></div></td>
<td><div>{$row.domain}</div></td>
<td><div>{$row.type}</div></td>
<td><span class="lu-label lu-tooltip lu-label--{$row.tone} lu-label--sm">{$row.label}</span></td>
<td><div>{$row.attempts}</div></td>
<td><div>{$row.error_message}</div></td>
<td><div>{$row.updated_at}</div></td>
<td class="lu-cell-actions lu-single-element">
    {if $row.canReset}
        <button type="button" class="lu-btn lu-btn--link lu-btn--plain lu-tooltip lu-btn--icon lu-btn--warning"
                aria-label="{t key='data_tables.queue.buttons.reset_task_button.resetTask'}" data-title="{t key='data_tables.queue.buttons.reset_task_button.resetTask'}"
                hx-post="{url action='reset' id=$row.id}" hx-target="#tssggs-queue"
                hx-confirm="{t key='data_tables.queue.modals.reset_task_modal.description'}"
                data-confirm-title="{t key='data_tables.queue.modals.reset_task_modal.title'}">
            <span class="lu-btn__icon tssggs-icobox">{icon name='restore'}</span>
        </button>
    {/if}
</td>
