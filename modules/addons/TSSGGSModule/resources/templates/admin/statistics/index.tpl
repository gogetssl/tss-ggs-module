{*
    Statistics page. The charts come in after the page is shown (each one reads all the orders): until then their
    place shows a spinner. The two lists at the end are part of the page.
*}
{if $showInfo}
    {include file='admin/partials/alert.tpl' text={t key='statistics_container.statisticsInfo'}}
{/if}

<div class="lu-container lu-w-100">
    <div class="lu-row">
        {foreach $charts as $chart}
            <div class="lu-col-md-6">
                <div class="lu-widget">
                    <div class="lu-widget__header">
                        <div class="lu-widget__top lu-top">
                            <span class="lu-widget__icon">{icon name=$chart.icon}</span>
                            <div class="lu-top__title">{$chart.title}</div>
                        </div>
                    </div>
                    <div class="lu-widget__body">
                        <div class="lu-widget__content lu-single-element">
                            <div class="lu-graph">
                                {include file='admin/partials/lazy.tpl' url={url action='chart' name=$chart.name}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}

        <div class="lu-col-md-6">
            <div class="lu-widget">
                <div class="lu-widget__header">
                    <div class="lu-widget__top lu-top">
                        <span class="lu-widget__icon">{icon name='account-group-outline'}</span>
                        <div class="lu-top__title">{t key='widgets.resellers_widget.title'}</div>
                    </div>
                </div>
                <div class="lu-widget__body">
                    <div class="lu-widget__content lu-single-element">
                        <table class="lu-table lu-table--mob-collapsible no-footer lu-table-simple" role="grid">
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

    <div class="lu-row">
        <div class="lu-col-md-12">
            <div class="lu-widget">
                <div class="lu-widget__header">
                    <div class="lu-widget__top lu-top">
                        <span class="lu-widget__icon">{icon name='file-document-outline'}</span>
                        <div class="lu-top__title">{t key='widgets.invoices_widget.title'}</div>
                    </div>
                </div>
                <div class="lu-widget__body">
                    <div class="lu-widget__content lu-single-element">
                        <table class="lu-table lu-table--mob-collapsible no-footer lu-table-simple" role="grid">
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
                                        <td><span class="lu-label lu-tooltip lu-label--{if $invoice.paid}success{else}danger{/if} lu-label--sm">{$invoice.status}</span></td>
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
