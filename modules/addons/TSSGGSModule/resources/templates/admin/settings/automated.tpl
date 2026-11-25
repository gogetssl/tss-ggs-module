{*
    Settings > Automated SSL: AutoInstall (cPanel) and the renewal of subscriptions (ACME / AutoInstall plans).
    $extra (App\Libs\Settings\SslScreen): checkSubscription, pendingWaitRisk.
*}
{include file='admin/settings/section_start.tpl' name='autoInstall' icon='cog-outline'}
    {* DNS wait for wildcard names, TLS check of the cPanel servers, the automatic reissue, the daily report *}
    {include file='admin/settings/field.tpl' type='text' name='wildcardDnsTimeoutMinutes'}
    {include file='admin/settings/field.tpl' type='yesno' name='cpanelTlsVerifyDisabled'}
    {include file='admin/settings/field.tpl' type='select' name='autoReissueDaysBeforeExpire'}
    {include file='admin/settings/field.tpl' type='yesno' name='autoInstallReportEmail'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='renewalsSubscription' icon='calendar-month-outline'}
    {include file='admin/settings/section_start.tpl' name='recurringOrders' sub=true}
        <div x-data="{ on: {if in_array($data.recurringCreateAutomaticRenewalInvoiceSubscription|default:'0', ['1', 'on'])}true{else}false{/if} }"
             @change="if ($event.target.name === 'recurringCreateAutomaticRenewalInvoiceSubscription') { on = $event.target.value === '1' }">
            {include file='admin/settings/field.tpl' type='yesno' name='recurringCreateAutomaticRenewalInvoiceSubscription'}
            <div x-show="on" x-cloak>
                {include file='admin/settings/field.tpl' type='select' name='recurringDaysBeforeExpirySubscription'}
                {include file='admin/settings/field.tpl' type='select' name='recurringPaymentTermSubscription'}
                {include file='admin/settings/renewal_check.tpl' check=$extra.checkSubscription}
                {include file='admin/settings/field.tpl' type='yesno' name='recurringSendExpirationNotificationsSubscription'}
                {include file='admin/settings/field.tpl' type='yesno' name='terminateUnpaidSubscriptions'}
                {include file='admin/settings/field.tpl' type='select' name='pendingPaymentWaitSubscription'}
                {if $extra.pendingWaitRisk}
                    {* waiting past the renewal day: the vendor charges before the payment has cleared *}
                    <span><div class="tssggs-renewal-check"><p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--amber">{t key="{$f}.pendingWaitSubscriptionRisk"}</p></div></span>
                {/if}
            </div>
        </div>
        {include file='admin/settings/field.tpl' type='select' name='subscriptionSlotPricing'}
    {include file='admin/settings/section_end.tpl'}
{include file='admin/settings/section_end.tpl'}

<script src="{$assets}/assets/js/custom/renewalCheck.js?v={$version}"></script>
