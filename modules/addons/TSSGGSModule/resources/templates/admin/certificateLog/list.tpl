{* The list of events with its pager: the part of the Certificate Log that htmx reloads *}
{if $flash}
    <div class="tssggs-note is-{$flash.tone}">{$flash.text}</div>
{/if}

<table class="table table-striped">
    <thead>
        <tr>
            <th style="width:150px">{t key='date'}</th>
            <th style="width:140px">{t key='service'}</th>
            <th style="width:200px">{t key='client'}</th>
            <th style="width:80px">{t key='type'}</th>
            <th>{t key='message'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach $events as $event}
            <tr>
                <td>{$event.date}</td>
                <td>
                    <a href="clientsservices.php?userid={$event.clientId}&amp;id={$event.serviceId}">#{$event.serviceId}</a>
                    {if $event.domain}<br><small>{$event.domain}</small>{/if}
                </td>
                <td><a href="clientssummary.php?userid={$event.clientId}">{$event.client}</a></td>
                <td class="tssggs-log__type tssggs-log__type--{$event.type}">{t key="type_{$event.type}"}</td>
                <td>{$event.message}</td>
            </tr>
        {foreachelse}
            <tr><td colspan="5" style="text-align:center">{t key='empty'}</td></tr>
        {/foreach}
    </tbody>
</table>

{if $pages > 1}
    <div class="tssggs-log__pager" hx-target="#tssggs-log-list" hx-push-url="true">
        {t key='page' page=$pageNo pages=$pages total=$total}
        {if $pageNo > 1}
            <a class="lu-btn lu-btn--default" href="{url service=$filter.service type=$filter.type search=$filter.search p=$pageNo-1}"
               hx-get="{url service=$filter.service type=$filter.type search=$filter.search p=$pageNo-1}">&laquo;</a>
        {/if}
        {if $pageNo < $pages}
            <a class="lu-btn lu-btn--default" href="{url service=$filter.service type=$filter.type search=$filter.search p=$pageNo+1}"
               hx-get="{url service=$filter.service type=$filter.type search=$filter.search p=$pageNo+1}">&raquo;</a>
        {/if}
    </div>
{/if}
