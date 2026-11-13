<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert">

    {if $subscription && $subscription->status}
        {assign var=aisStatus value=$subscription->status|lower}
    {else}
        {assign var=aisStatus value='pending'}
    {/if}
    {if $aisStatus == 'active'}{assign var=aisTone value='ok'}
    {elseif $aisStatus == 'cancelled' || $aisStatus == 'terminated'}{assign var=aisTone value='off'}
    {else}{assign var=aisTone value='wait'}{/if}

    {* ---------- Hero: subscription state, the way in, and the actions ---------- *}
    <section class="tssggs-card tssggs-hero tssggs-hero--{$aisTone}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--{$aisTone}">{$aisStatus|capitalize|escape}</span>
                <h3>{$productName|escape}</h3>
                {if $subscription && $subscription->autoinstall_status}
                    <p class="tssggs-hero__domain">{$MGLANG->T('aisAutoinstallStatus')}: {$subscription->autoinstall_status|capitalize|escape}</p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <div class="tssggs-actions">
                {if $subscription && $aisInstallMethod != 'acme' && $subscription->login_sso_link}
                    <a class="tssggs-btn tssggs-btn--primary" href="{$subscription->login_sso_link|escape:'htmlall'}" target="_blank" rel="noopener">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        {$MGLANG->T('aisLoginLink')}
                    </a>
                {/if}
                {if $subscription && $aisInstallMethod != 'acme' && $subscription->manage_sso_link}
                    <a class="tssggs-btn" href="{$subscription->manage_sso_link|escape:'htmlall'}" target="_blank" rel="noopener">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        {$MGLANG->T('aisManageLink')}
                    </a>
                {/if}
                <button id="ais-refresh" type="button" class="tssggs-btn">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    {$MGLANG->T('aisRefreshDetails')}
                </button>
                {if $subscription && $subscription->api_order_id}
                    {if $aisAutoRenew}
                        <button id="ais-stop-auto-renewal" type="button" class="tssggs-btn">{$MGLANG->T('aisStopAutoRenewal')}</button>
                    {/if}
                    {if $subscription->status|lower != 'cancelled'}
                        {if $aisCanSwitch}
                            <button id="ais-switch-method" type="button" class="tssggs-btn" data-method="{if $aisInstallMethod == 'acme'}autoinstall{else}acme{/if}">
                                {if $aisInstallMethod == 'acme'}{$MGLANG->T('aisSwitchToAutoinstall')}{else}{$MGLANG->T('aisSwitchToAcme')}{/if}
                            </button>
                        {/if}
                        <button id="ais-cancel-subscription" type="button" class="tssggs-btn tssggs-btn--danger">{$MGLANG->T('aisCancelSubscription')}</button>
                    {/if}
                {/if}
            </div>
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MGLANG->T('aisOrderStatus')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('aisProductName')}</dt><dd>{$productName|escape}</dd>
                {if $subscription}
                    {if $subscription->renewal_date}<dt>{$MGLANG->T('aisRenewalDate')}</dt><dd>{$subscription->renewal_date|escape}</dd>{/if}
                    <dt>{$MGLANG->T('acmeAutoRenew')}</dt>
                    <dd>{if $aisAutoRenew}<span class="tssggs-badge">{$MGLANG->T('acmeStatusActive')}</span>{else}<span class="tssggs-badge tssggs-badge--off">{$MGLANG->T('acmeStatusRemoved')}</span>{/if}</dd>
                    <dt>{$MGLANG->T('aisInstallMethod')}</dt><dd>{$MGLANG->T('aisMethod_'|cat:$aisInstallMethod)}</dd>
                {/if}
            </dl>
        </section>

        {if $subscription && $aisInstallMethod == 'acme'}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 18v3h3l11-11-3-3L2 18z"/><path d="m15 5 4 4"/><circle cx="18" cy="6" r="3"/></svg>
                {$MGLANG->T('acmeAccountSection')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('acmeAccountId')}</dt><dd class="tssggs-mono">{$subscription->acme_account_id|escape}</dd>
                <dt>{$MGLANG->T('acmeEabKid')}</dt><dd class="tssggs-mono">{$subscription->eab_kid|escape}</dd>
                <dt>{$MGLANG->T('acmeEabHmacKey')}</dt><dd class="tssggs-mono" style="word-break:break-all;">{$subscription->eab_hmac_key|escape}</dd>
                <dt>{$MGLANG->T('acmeServerUrl')}</dt><dd class="tssggs-mono" style="word-break:break-all;">{$subscription->server_url|escape}</dd>
            </dl>
        </section>
        {/if}
    </div>

<div class="modal fade" id="aisStopAutoRenewalModal" tabindex="-1" role="dialog" aria-labelledby="aisStopAutoRenewalModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="aisStopAutoRenewalModalLabel">{$MGLANG->T('aisStopAutoRenewal')}</h4>
            </div>
            <div class="modal-body">
                {$MGLANG->T('aisStopAutoRenewalConfirm')}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                <button type="button" class="btn btn-warning" id="aisConfirmStopAutoRenewal">{$MGLANG->T('aisStopAutoRenewal')}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aisCancelSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="aisCancelSubscriptionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="aisCancelSubscriptionModalLabel">{$MGLANG->T('aisCancelSubscription')}</h4>
            </div>
            <div class="modal-body">
                {$MGLANG->T('aisCancelSubscriptionConfirm')}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                <button type="button" class="btn btn-danger" id="aisConfirmCancelSubscription">{$MGLANG->T('aisCancelSubscription')}</button>
            </div>
        </div>
    </div>
</div>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mg-module .validate-ssl-unified').remove();
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
    });

    $(document).ready(function () {
        var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';

        function runAction(action, data, options) {
            options = options || {};
            data = data || {};
            data['mg-action'] = action;
            data['token'] = '{$csrfToken}';
            $.post(serviceUrl, data, function (ret) {
                // the JSON answer comes with an application/json header, so jQuery may already have parsed it
                var payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
                if (payload.result === 'error') {
                    var errorMessage = payload.error || 'Error';
                    if (typeof options.onError === 'function') {
                        options.onError(errorMessage, payload);
                    } else {
                        $('#MGAlerts').alerts('error', errorMessage);
                    }
                    return;
                }
                var msg = (payload.data && payload.data.message) ? payload.data.message : 'Done';
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(payload);
                }
                $('#MGAlerts').alerts('success', msg);
                window.setTimeout(function () { window.location.reload(); }, 1200);
            });
        }

        $('body').on('click', '#ais-refresh', function () {
            runAction('refreshAisDetails', {});
        });

        $('body').on('click', '#ais-switch-method', function () {
            if (!window.confirm('{$MGLANG->T('aisSwitchConfirm')|escape:'javascript'}')) {
                return;
            }
            runAction('changeAisInstallMethod', {ldelim}install_method: $(this).data('method'){rdelim});
        });

        $('body').on('click', '#ais-stop-auto-renewal', function () {
            $('#aisStopAutoRenewalModal').modal('show');
        });

        $('body').on('click', '#ais-cancel-subscription', function () {
            $('#aisCancelSubscriptionModal').modal('show');
        });

        $('body').on('click', '#aisConfirmStopAutoRenewal', function () {
            $('#aisStopAutoRenewalModal').modal('hide');
            runAction('stopAisAutoRenewal', {});
        });

        $('body').on('click', '#aisConfirmCancelSubscription', function () {
            $('#aisCancelSubscriptionModal').modal('hide');
            runAction('cancelAisSubscription', {});
        });
    });
</script>
{/if}
