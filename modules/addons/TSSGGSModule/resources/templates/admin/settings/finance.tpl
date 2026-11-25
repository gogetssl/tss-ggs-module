{*
    Settings > Finance: how prices are made from the reseller cost, what happens when a price falls below cost,
    renewal prices of existing services, currency, billing, and the reseller balance alert.
    $extra (App\Libs\Settings\FinanceScreen): rules, baseCurrency, needsRate, accounts.
*}
{include file='admin/settings/section_start.tpl' name='pricing' icon='trending-up'}
    {* the other places that decide a price, with what is set there now *}
    <span>
        <div class="tssggs-sec__desc tssggs-sec__desc--rules">
            <p>{t key="{$f}.rulesIntro"}</p>
            <ul>
                {foreach $extra.rules as $rule}
                    <li><b>{t key="{$f}.{$rule.key}"}</b> {t key="{$f}.{$rule.key}_text" auto=$rule.auto manual=$rule.manual count=$rule.count global=$rule.global} <a href="{url page=$rule.page}">{t key="{$f}.{$rule.link}"}</a></li>
                {/foreach}
            </ul>
        </div>
    </span>
    {include file='admin/settings/field.tpl' type='number' name='profitMargin' class='tssggs-percent' min=0 max=1000}
    {if $extra.baseCurrency !== ''}
        {* the amount field shows the currency after the number: USD comes from settings.css, another base currency from here *}
        <style>#layers2 .tssggs-settings form .tssggs-sec .lu-form-group:has(> .tssggs-money)::after {ldelim} content: "{$extra.baseCurrency}"; {rdelim}</style>
    {/if}
    {include file='admin/settings/field.tpl' type='text' name='minProfit' class='tssggs-money'}
    {include file='admin/settings/field.tpl' type='number' name='termMarginStep' class='tssggs-percent' min=0 max=100}
    {include file='admin/settings/field.tpl' type='select' name='priceRounding'}
    {include file='admin/settings/field.tpl' type='yesno' name='keepMarginOnSync'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='belowCost' icon='alert-circle-outline'}
    {include file='admin/settings/field.tpl' type='select' name='belowCostAction'}
    {include file='admin/settings/field.tpl' type='yesno' name='belowCostEmail'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='renewalPrices' icon='refresh' points=['renewalPoint_never', 'renewalPoint_belowCost', 'renewalPoint_always']}
    {include file='admin/settings/field.tpl' type='select' name='renewalPriceMode'}
    {include file='admin/settings/field.tpl' type='yesno' name='renewalPriceClientEmail'}
    {include file='admin/settings/field.tpl' type='textarea' name='renewalPriceEmailText'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='currency' icon='currency-usd'}
    {if $extra.needsRate}
        {* the vendor prices are in USD: without a USD currency in WHMCS the module converts with its own rate *}
        {include file='admin/settings/field.tpl' type='select' name='rateSource'}
        {include file='admin/settings/field.tpl' type='text' name='rate'}
    {/if}
    {include file='admin/settings/field.tpl' type='text' name='currencyBuffer' class='tssggs-percent'}
{include file='admin/settings/section_end.tpl'}

{* no fields: unpaid certificates are terminated by WHMCS's own automation, the description says where *}
{include file='admin/settings/section_start.tpl' name='billing' icon='credit-card-clock-outline'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='balance' icon='hand-coin-outline'}
    {include file='admin/settings/field.tpl' type='yesno' name='balanceAlert'}
    {foreach $extra.accounts as $account}
        {* what the last check of this vendor account found; then the amount below which the administrators are warned *}
        <span>
            <div class="tssggs-renewal-check">
                <p class="{if $account.tone === 'warn'}tssggs-renewal-check__warning tssggs-renewal-check__warning--amber{elseif $account.tone === 'ok'}tssggs-renewal-check__warning tssggs-renewal-check__warning--green{else}tssggs-renewal-check__summary{/if}">{$account.html nofilter}</p>
            </div>
        </span>
        {if $account.threshold}
            {include file='admin/settings/field.tpl' type='text' name=$account.threshold.name label=$account.threshold.label hint=$account.threshold.hint}
        {/if}
    {/foreach}
{include file='admin/settings/section_end.tpl'}
