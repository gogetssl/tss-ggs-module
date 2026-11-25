{*
    One record of a report ($row): a cell per column of the report, in the columns' order.
    clientDetails and productDetails are ready HTML (two lines; the library escaped the values).
*}
{foreach $table.columns as $column}
    {$name = $column.name}
    <td>
        {if $name === 'status'}
            <span class="lu-label lu-tooltip lu-label--{$row.statusTone} lu-label--sm">{$row.status}</span>
        {elseif $name === 'paymentStatus'}
            <span class="lu-label lu-tooltip lu-label--{$row.paymentTone} lu-label--sm">{$row.paymentStatus}</span>
        {elseif $name === 'salesAmount'}
            <div>{$row.salesAmountText nofilter}</div>
        {elseif $name === 'cost'}
            <div>{$row.costText nofilter}</div>
        {elseif $name === 'grossProfit'}
            <div>{$row.grossProfitText nofilter}</div>
        {elseif $name === 'clientDetails' || $name === 'productDetails'}
            <div>{$row.$name nofilter}</div>
        {else}
            <div>{$row.$name}</div>
        {/if}
    </td>
{/foreach}
