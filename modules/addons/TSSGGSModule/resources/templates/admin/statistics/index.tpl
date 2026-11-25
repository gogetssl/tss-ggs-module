{*
    Statistics page. The charts come in after the page is shown (each one reads all the orders): until then their
    place shows a spinner. The two lists at the end are part of the page.
*}
{if $showInfo}
    {include file='admin/partials/alert.tpl' text={t key='statistics_container.statisticsInfo'}}
{/if}

<div class="mod-wrap mod-w-full">
    <div class="mod-row">
        {foreach $charts as $chart}
            <div class="mod-col-6">
                <div class="mod-box">
                    <div class="mod-box-head">
                        <div class="mod-box-top mod-bar">
                            <span class="mod-box-icon">{icon name=$chart.icon}</span>
                            <div class="mod-bar-title">{$chart.title}</div>
                        </div>
                    </div>
                    <div class="mod-box-body">
                        <div class="mod-box-content mod-solo">
                            <div class="mod-graph">
                                {include file='admin/partials/lazy.tpl' url={url action='chart' name=$chart.name}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}

        <div class="mod-col-6">
            <div class="mod-box">
                <div class="mod-box-head">
                    <div class="mod-box-top mod-bar">
                        <span class="mod-box-icon">{icon name='account-group-outline'}</span>
                        <div class="mod-bar-title">{t key='widgets.resellers_widget.title'}</div>
                    </div>
                </div>
                <div class="mod-box-body">
                    <div class="mod-box-content mod-solo">
                        <table class="mod-table mod-table--stack mod-table--plain" role="grid">
                            <thead>
                                <tr>
                                    <th>{t key='widgets.resellers_widget.clientId'}</th>
                                    <th>{t key='widgets.resellers_widget.client'}</th>
                                    <th>{t key='widgets.resellers_widget.paid'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $clients as $client}
                                    <tr>
                                        <td><span>{$client.id}</span></td>
                                        <td><a href="clientssummary.php?userid={$client.id}">{$client.name}</a></td>
                                        <td><span>{$client.paid}</span></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mod-row">
        <div class="mod-col-12">
            <div class="mod-box">
                <div class="mod-box-head">
                    <div class="mod-box-top mod-bar">
                        <span class="mod-box-icon">{icon name='file-document-outline'}</span>
                        <div class="mod-bar-title">{t key='widgets.invoices_widget.title'}</div>
                    </div>
                </div>
                <div class="mod-box-body">
                    <div class="mod-box-content mod-solo">
                        <table class="mod-table mod-table--stack mod-table--plain" role="grid">
                            <thead>
                                <tr>
                                    <th>{t key='widgets.invoices_widget.invoiceId'}</th>
                                    <th>{t key='widgets.invoices_widget.client'}</th>
                                    <th>{t key='widgets.invoices_widget.status'}</th>
                                    <th>{t key='widgets.invoices_widget.amount'}</th>
                                    <th>{t key='widgets.invoices_widget.date'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $invoices as $invoice}
                                    <tr>
                                        <td><span>{$invoice.id}</span></td>
                                        <td><a href="clientssummary.php?userid={$invoice.clientId}">{$invoice.client}</a></td>
                                        <td><span class="mod-tag mod-tip mod-tag--{if $invoice.paid}success{else}danger{/if} mod-tag--sm">{$invoice.status}</span></td>
                                        <td><span>{$invoice.amount}</span></td>
                                        <td><span>{$invoice.date}</span></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{$assets}/assets/js/vendor/apexcharts.min.js?v={$version}"></script>
<script src="{$assets}/assets/js/charts.js?v={$version}"></script>
