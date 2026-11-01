<div class="tssggs-client">
    {if $byDomain}
        <div class="tssggs-views" style="margin-bottom:15px;">
            <a class="btn btn-sm {if $page == 'certificates'}btn-primary{else}btn-default{/if}" href="{$moduleUrl}">{$t.listView}</a>
            <a class="btn btn-sm {if $page == 'domains'}btn-primary{else}btn-default{/if}" href="{$moduleUrl}&amp;page=domains">{$t.byDomain}</a>
        </div>
    {/if}

    <ul class="nav nav-tabs tssggs-filters" role="tablist">
        {foreach ['total', 'unpaid', 'processing', 'expires_soon'] as $filter}
            <li class="nav-item{if $type == $filter} active{/if}">
                <a class="nav-link{if $type == $filter} active{/if}" href="{$moduleUrl}&amp;type={$filter}">{$t.$filter} <span class="badge">{$counts.$filter}</span></a>
            </li>
        {/foreach}
    </ul>

    <div class="table-container clearfix">
        <table class="table table-list">
            <thead>
            <tr>
                <th>{$t.domain}</th>
                <th>{$t.product}</th>
                <th>{$t.status}</th>
                <th>{$t.nextDueDate}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach $services as $service}
                <tr>
                    <td><strong>{$service.domain|escape|default:'-'}</strong></td>
                    <td>{$service.product|escape}</td>
                    <td>
                        {if $service.order_status}
                            {assign var=statusKey value="status_`$service.order_status`"}
                            <span class="label status status-{$service.order_status|escape}">{$t.$statusKey|default:$service.order_status|escape}</span>
                        {elseif $service.ssl_status == 'Awaiting Configuration'}
                            <span class="label status status-pending">{$t.status_awaiting}</span>
                        {else}
                            <span class="label status status-{$service.service_status|lower|escape}">{$service.service_status|escape}</span>
                        {/if}
                    </td>
                    <td>{if $service.nextduedate && $service.nextduedate != '0000-00-00'}{$service.nextduedate|escape}{else}-{/if}</td>
                    <td class="text-right">
                        {if $service.unpaid_invoice}
                            <a class="btn btn-sm btn-success" href="viewinvoice.php?id={$service.unpaid_invoice}">{$t.payInvoice}</a>
                        {/if}
                        <a class="btn btn-sm btn-default" href="clientarea.php?action=productdetails&amp;id={$service.id}">{$t.manage}</a>
                    </td>
                </tr>
            {foreachelse}
                <tr><td colspan="5" class="text-center">{$t.noCertificates}</td></tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
