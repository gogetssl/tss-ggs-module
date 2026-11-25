{*
    One report ($name): the summary line, the filters, the list. "Search" reloads this whole block
    (#tssggs-report-<name>); the list's own search, sorting and paging reload only the list.
    $has.<filter> says which filters the report has; $texts is the group of its texts.
*}
{if $summary}
    <div class="lu-alert lu-alert--faded lu-alert--secondary" data-component="alert">
        <div class="lu-alert__body">
            {foreach $summary as $line}
                <p class="report-header{if !$line@first} rh-second{/if}">
                    {foreach $line as $item}{$item.label} <span class="{$item.color}">{$item.value nofilter} </span>{/foreach}
                </p>
            {/foreach}
        </div>
    </div>
{/if}

<div class="lu-widget">
    <div class="lu-widget__body">
        <div class="lu-widget__content lu-single-element">
            <div class="lu-container lu-w-100">
                <div class="lu-row">
                    <div class="lu-col-md-6" x-data="{ advanced: {if $advanced}true{else}false{/if} }">
                        <form method="get" action="addonmodules.php" class="lu-w-100"
                              hx-get="addonmodules.php" hx-target="#tssggs-report-{$name}" hx-swap="innerHTML">
                            <input type="hidden" name="module" value="{$moduleName}">
                            <input type="hidden" name="mod-page" value="{$pageName}">
                            <input type="hidden" name="mod-action" value="report">
                            <input type="hidden" name="name" value="{$name}">
                            <div class="lu-container-fluid">
                                <div class="lu-row">
                                    {if $has.renewalPeriod|default:false}
                                        <div class="lu-form-group lu-col-md-12">
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.renewalPeriod"}</span></label>
                                            <select name="renewalPeriod" class="lu-form-control">
                                                {foreach $periods as $value => $text}
                                                    <option value="{$value}"{if $filters.renewalPeriod === $value} selected{/if}>{t key="{$texts}.providers.filter_provider.{$text}"}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}
                                    {if $has.fromDate|default:false}
                                        <div class="lu-form-group lu-col-md-6">
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.fromDate"}</span></label>
                                            <input type="date" name="fromDate" class="lu-form-control" value="{$filters.fromDate}">
                                        </div>
                                        <div class="lu-form-group lu-col-md-6">
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.toDate"}</span></label>
                                            <input type="date" name="toDate" class="lu-form-control" value="{$filters.toDate}">
                                        </div>
                                    {/if}
                                    {if $has.brand|default:false}
                                        {* the advanced filters: sent only while they are shown *}
                                        <div class="lu-form-group lu-col-md-6" x-show="advanced" x-cloak>
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.brand"}</span></label>
                                            <select name="brand" class="lu-form-control" :disabled="!advanced">
                                                <option value="">{t key="{$texts}.providers.filter_provider.all"}</option>
                                                {foreach $brands as $brand}
                                                    <option value="{$brand}"{if $filters.brand === $brand} selected{/if}>{$brand}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="lu-form-group lu-col-md-6" x-show="advanced" x-cloak>
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.productId"}</span></label>
                                            <select name="productId" class="lu-form-control" :disabled="!advanced">
                                                <option value="">{t key="{$texts}.providers.filter_provider.all"}</option>
                                                {foreach $products as $id => $product}
                                                    <option value="{$id}"{if $filters.productId == $id} selected{/if}>{$product}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="lu-form-group lu-col-md-6" x-show="advanced" x-cloak>
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.sslStatus"}</span></label>
                                            <select name="sslStatus" class="lu-form-control" :disabled="!advanced">
                                                <option value="">{t key="{$texts}.providers.filter_provider.all"}</option>
                                                {foreach $statuses as $status}
                                                    <option value="{$status}"{if $filters.sslStatus === $status} selected{/if}>{t key="{$texts}.providers.filter_provider.{$status}"}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="lu-form-group lu-col-md-6" x-show="advanced" x-cloak>
                                            <label class="lu-form-label"><span>{t key="{$texts}.forms.filter_form.vendorOrderId"}</span></label>
                                            <input type="text" name="vendorOrderId" class="lu-form-control" value="{$filters.vendorOrderId}" :disabled="!advanced">
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            <div class="lu-top__toolbar">
                                <button type="submit" class="lu-btn lu-btn--success">
                                    <span class="lu-btn__icon tssggs-icobox">{icon name='magnify'}</span><span class="lu-btn__text">{t key="{$texts}.forms.filter_form.search"}</span>
                                </button>
                                {if $has.brand|default:false}
                                    <button type="button" class="lu-btn lu-btn--default" @click="advanced = !advanced">
                                        <span class="lu-btn__text" x-show="!advanced">{t key="{$texts}.forms.filter_form.advancedSearch"}</span>
                                        <span class="lu-btn__text" x-show="advanced" x-cloak>{t key="{$texts}.forms.filter_form.basicSearch"}</span>
                                    </button>
                                {/if}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{include file='admin/partials/table.tpl'}
