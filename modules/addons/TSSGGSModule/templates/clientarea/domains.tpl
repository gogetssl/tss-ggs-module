<div class="tssggs-client">
    {if $byDomain}
        <div class="tssggs-views" style="margin-bottom:15px;">
            <a class="btn btn-sm {if $page == 'certificates'}btn-primary{else}btn-default{/if}" href="{$moduleUrl}">{$t.listView}</a>
            <a class="btn btn-sm {if $page == 'domains'}btn-primary{else}btn-default{/if}" href="{$moduleUrl}&amp;page=domains">{$t.byDomain}</a>
        </div>
    {/if}

    <form method="get" action="index.php" class="form-inline tssggs-domain-search" style="margin-bottom:15px;">
        <input type="hidden" name="m" value="TSSGGSModule">
        <input type="hidden" name="page" value="domains">
        <input type="text" name="search" value="{$search|escape}" class="form-control" placeholder="{$t.searchPlaceholder}">
        <select name="status" class="form-control">
            <option value="">{$t.allStatuses}</option>
            <option value="active"{if $status == 'active'} selected{/if}>{$t.activeOnly}</option>
            <option value="inactive"{if $status == 'inactive'} selected{/if}>{$t.inactiveOnly}</option>
        </select>
        <button type="submit" class="btn btn-default">{$t.search}</button>
    </form>

    <div class="table-container clearfix">
        <table class="table table-list">
            <thead>
            <tr>
                <th>{$t.domain}</th>
                <th>{$t.certificate}</th>
                <th>{$t.status}</th>
                <th>{$t.validTill}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach $domains as $row}
                {assign var=statusKey value="status_`$row.current.status`"}
                <tr>
                    <td><strong>{$row.domain|escape}</strong></td>
                    <td>{$row.current.product|escape} <small class="text-muted">#{$row.current.service_id}</small></td>
                    <td><span class="label status status-{$row.current.status|escape}">{$t.$statusKey|default:$row.current.status|escape}</span></td>
                    <td>{$row.current.valid_till|default:'-'}</td>
                    <td class="text-right">
                        {if $row.history}
                            <button type="button" class="btn btn-sm btn-default" data-toggle="collapse" data-target="#tssggs-history-{$row@index}">{$t.history} ({$row.history|@count})</button>
                        {/if}
                        <a class="btn btn-sm btn-default" href="clientarea.php?action=productdetails&amp;id={$row.current.service_id}">{$t.manage}</a>
                    </td>
                </tr>
                {if $row.history}
                    <tr id="tssggs-history-{$row@index}" class="collapse">
                        <td colspan="5" style="background:#fafafa;">
                            <table class="table table-condensed" style="margin:0;">
                                <tr><th>{$t.certificate}</th><th>{$t.ordered}</th><th>{$t.validFrom}</th><th>{$t.validTill}</th><th>{$t.status}</th></tr>
                                {foreach $row.history as $old}
                                    {assign var=oldStatusKey value="status_`$old.status`"}
                                    <tr>
                                        <td><a href="clientarea.php?action=productdetails&amp;id={$old.service_id}">{$old.product|escape} #{$old.service_id}</a></td>
                                        <td>{$old.ordered|escape}</td>
                                        <td>{$old.valid_from|default:'-'}</td>
                                        <td>{$old.valid_till|default:'-'}</td>
                                        <td>{$t.$oldStatusKey|default:$old.status|escape}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>
                {/if}
            {foreachelse}
                <tr><td colspan="5" class="text-center">{$t.noDomains}</td></tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
