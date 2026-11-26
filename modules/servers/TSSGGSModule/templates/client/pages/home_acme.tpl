<script type="text/javascript" src="{$assetsURL}/js/client.js"></script>

{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert">

    {if $subscription && $subscription->status}
        {assign var=acmeStatus value=$subscription->status|lower}
    {else}
        {assign var=acmeStatus value='pending'}
    {/if}
    {if $acmeStatus == 'active'}{assign var=acmeTone value='ok'}
    {elseif $acmeStatus == 'cancelled' || $acmeStatus == 'terminated'}{assign var=acmeTone value='off'}
    {else}{assign var=acmeTone value='wait'}{/if}

    {* ---------- Hero: subscription state and what the client can do ---------- *}
    <section class="tssggs-card tssggs-hero tssggs-hero--{$acmeTone}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--{$acmeTone}">{$acmeStatus|capitalize|escape}</span>
                <h3>{$productName|escape}</h3>
                <p class="tssggs-hero__domain">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
                    {$text->t('acmeSingleDomainsLabel')}: {$singleSansCurrent|intval} / {$singleSansPurchased|intval}{if $wildcardSansPurchased} &middot; {$text->t('acmeWildcardDomainsLabel')}: {$wildcardSansCurrent|intval} / {$wildcardSansPurchased|intval}{/if}
                </p>
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <div class="tssggs-actions">
                {if !$subscription || !$subscription->api_order_id}
                    <button class="tssggs-btn tssggs-btn--primary acme-action" data-action="createSubscription">{$text->t('acmeCreateSubscription')}</button>
                {else}
                    {if $canAddDomains}
                        <button class="tssggs-btn tssggs-btn--primary acme-action" data-action="addDomain">{$text->t('acmeAddDomainButton')}</button>
                    {/if}
                    <button class="tssggs-btn acme-action" data-action="openUpgradeDomains">{$text->t('acmeBuyMoreDomains')}</button>
                    {if !$subscription->status || $subscription->status|lower != 'active'}
                        <button class="tssggs-btn acme-action" data-action="checkCertificateDetails">{$text->t('acmeCheckCertificateDetails')}</button>
                    {/if}
                    {if $subscription->auto_renew}
                        <button class="tssggs-btn acme-action" data-action="stopAutoRenewal">{$text->t('acmeStopAutoRenewal')}</button>
                    {/if}
                    {if !$endsAtPeriodEnd}
                        <button class="tssggs-btn tssggs-btn--danger acme-action" data-action="cancelSubscription">{$text->t('acmeCancelSubscription')}</button>
                    {/if}
                {/if}
            </div>
            {if $endsAtPeriodEnd}<p class="tssggs-note" id="acme-ends-note">{$endsAtPeriodEndNote}</p>{/if}
            {if $renewalUnpaid}<p class="tssggs-note" id="acme-renewal-unpaid">{$text->t('subscriptionRenewalUnpaid')}</p>{/if}
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$text->t('acmeOrderStatus')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$text->t('acmeProductName')}</dt><dd>{$productName|escape}</dd>
                {if $subscription}
                    <dt>{$text->t('acmeRenewalDate')}</dt><dd>{if $subscription->renewal_date}{$subscription->renewal_date|escape}{else}-{/if}</dd>
                    <dt>{$text->t('acmeAutoRenew')}</dt>
                    <dd>{if $subscription->auto_renew}<span class="tssggs-badge">{$text->t('acmeStatusActive')}</span>{else}<span class="tssggs-badge tssggs-badge--off">{$text->t('acmeStatusRemoved')}</span>{/if}</dd>
                {/if}
                <dt>{$text->t('acmeTotalSans')}</dt>
                <dd>
                    {$text->t('acmeSingleDomainsLabel')}: {$singleSansCurrent|intval} / {$singleSansPurchased|intval}
                    {if $wildcardSansPurchased}<br>{$text->t('acmeWildcardDomainsLabel')}: {$wildcardSansCurrent|intval} / {$wildcardSansPurchased|intval}
                    <br>{$text->t('acmeBothTypesLabel')}: {$totalSansCurrent|intval} / {$totalSansPurchased|intval}{/if}
                </dd>
            </dl>
        </section>

        {if $subscription}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 18v3h3l11-11-3-3L2 18z"/><path d="m15 5 4 4"/><circle cx="18" cy="6" r="3"/></svg>
                {$text->t('acmeAccountSection')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$text->t('acmeAccountId')}</dt><dd class="tssggs-mono">{$subscription->acme_account_id|escape}</dd>
                {if $acmeKeysUsable}
                <dt>{$text->t('acmeEabKid')}</dt><dd class="tssggs-mono">{$subscription->eab_kid|escape}</dd>
                <dt>{$text->t('acmeEabHmacKey')}</dt><dd class="tssggs-mono" style="word-break:break-all;">{$subscription->eab_hmac_key|escape}</dd>
                {else}
                <dt>{$text->t('acmeEabKid')}</dt><dd>{$text->t('acmeKeysPending')}</dd>
                {/if}
                <dt>{$text->t('acmeServerUrl')}</dt><dd class="tssggs-mono" style="word-break:break-all;">{$subscription->server_url|escape}</dd>
            </dl>
        </section>
        {/if}
    </div>

    <section class="tssggs-card">
        <h4 class="tssggs-card__title">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
            {$text->t('acmeDomains')}
        </h4>
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>{$text->t('domain')}</th>
                            <th>{$text->t('acmeDomainType')}</th>
                            <th>{$text->t('acmeDomainStatus')}</th>
                            <th>{$text->t('acmeDateAdded')}</th>
                            <th>{$text->t('Actions')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {if $domains|@count > 0}
                            {foreach $domains as $domainRow}
                                <tr>
                                    <td>{$domainRow->domain|escape}</td>
                                    <td>{$domainRow->domain_type|escape}</td>
                                    <td>
                                        {assign var=acmeDomainStatus value=$domainRow->status|lower}
                                        {if $acmeDomainStatus == 'added'}
                                            <span class="label label-success">{$text->t('acmeStatusActive')}</span>
                                        {elseif $acmeDomainStatus == 'removed'}
                                            <span class="label label-default">{$text->t('acmeStatusRemoved')}</span>
                                        {else}
                                            <span class="label label-info">{$acmeDomainStatus|capitalize|escape}</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $domainRow->addedOn}{$domainRow->addedOn|escape}{else}{$domainRow->added_at|escape}{/if}
                                        {if $domainRow->refundableTill}
                                            <br><small class="text-muted">{$text->t('acmeRefundableTill')|sprintf:$domainRow->refundableTill}</small>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $domainRow->status == 'added' && $subscription}
                                            <button class="btn btn-xs btn-default acme-action" data-action="removeDomain" data-domain="{$domainRow->domain|escape:'htmlall'}">{$text->t('acmeRemoveDomain')}</button>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="5">{$text->t('acmeNoDomains')}</td>
                            </tr>
                        {/if}
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="text-left">{$text->t('acmeTotalSans')}</td>
                <td class="text-left">
                    <span class="label label-default">{$text->t('acmeSingleDomainsLabel')}: {$singleSansCurrent|intval} / {$singleSansPurchased|intval}</span>
                    {if $wildcardSansPurchased}
                    <span class="label label-info">{$text->t('acmeWildcardDomainsLabel')}: {$wildcardSansCurrent|intval} / {$wildcardSansPurchased|intval}</span>
                    <span class="label label-success">{$text->t('acmeBothTypesLabel')}: {$totalSansCurrent|intval} / {$totalSansPurchased|intval}</span>
                    {/if}
                </td>
            </tr>
            <tr>
                <td class="text-left">{$text->t('Actions')}</td>
                <td class="text-left">
                    {if !$subscription || !$subscription->api_order_id}
                        <button style="margin-bottom: 10px;" class="btn btn-default acme-action" data-action="createSubscription">{$text->t('acmeCreateSubscription')}</button>
                    {else}
                        {if $canAddDomains}
                            <button style="margin-bottom: 10px;" class="btn btn-primary acme-action" data-action="addDomain">{$text->t('acmeAddDomainButton')}</button>
                        {/if}
                        <button style="margin-bottom: 10px;" class="btn btn-default acme-action" data-action="openUpgradeDomains">{$text->t('acmeBuyMoreDomains')}</button>
                        {if !$subscription->status || $subscription->status|lower != 'active'}
                            <button style="margin-bottom: 10px;" class="btn btn-default acme-action" data-action="checkCertificateDetails">{$text->t('acmeCheckCertificateDetails')}</button>
                        {/if}
                        {if $subscription->auto_renew}
                            <button style="margin-bottom: 10px;" class="btn btn-default acme-action" data-action="stopAutoRenewal">{$text->t('acmeStopAutoRenewal')}</button>
                        {/if}
                        {if !$endsAtPeriodEnd}
                            <button style="margin-bottom: 10px;" class="btn btn-danger acme-action" data-action="cancelSubscription">{$text->t('acmeCancelSubscription')}</button>
                        {else}
                            <p>{$endsAtPeriodEndNote}</p>
                        {/if}
                    {/if}
                </td>
            </tr>
    </section>

    <div class="modal fade" id="acmeUpgradeDomainsModal" tabindex="-1" role="dialog" aria-labelledby="acmeUpgradeDomainsModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="text-align: left;">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeUpgradeDomainsModalLabel">{$text->t('acmeBuyMoreDomains')}</h4>
                </div>
                <div class="modal-body">
                    <div id="acmeUpgradeDomainsError" class="alert alert-danger" style="display:none;"></div>
                    <div class="alert alert-info">
                        {$text->t('acmeSingleDomainsLabel')}: {$text->t('acmeCurrentlyActive')}: <strong>{$singleSansCurrent|intval}</strong> / {$text->t('acmePurchased')}: <strong>{$singleSansPurchased|intval}</strong>
                        {if $allowWildcard}
                            <br>{$text->t('acmeWildcardDomainsLabel')}: {$text->t('acmeCurrentlyActive')}: <strong>{$wildcardSansCurrent|intval}</strong> / {$text->t('acmePurchased')}: <strong>{$wildcardSansPurchased|intval}</strong>
                        {/if}
                    </div>
                    <div class="form-group">
                        <label for="acmeUpgradeSingleInput">{$text->t('acmeSingleDomainsLabel')} ({$text->t('acmeNewLimit')})</label>
                        <input type="number" id="acmeUpgradeSingleInput" class="form-control" min="{$singleSansCurrent|intval}" value="{$singleSansPurchased|intval}">
                        <span class="help-block">{$text->t('acmeMinimumValue')}: {$singleSansCurrent|intval} ({$text->t('acmeCurrentlyActive')})</span>
                    </div>
                    {if $allowWildcard}
                        <div class="form-group">
                            <label for="acmeUpgradeWildcardInput">{$text->t('acmeWildcardDomainsLabel')} ({$text->t('acmeNewLimit')})</label>
                            <input type="number" id="acmeUpgradeWildcardInput" class="form-control" min="{$wildcardSansCurrent|intval}" value="{$wildcardSansPurchased|intval}">
                            <span class="help-block">{$text->t('acmeMinimumValue')}: {$wildcardSansCurrent|intval} ({$text->t('acmeCurrentlyActive')})</span>
                        </div>
                    {/if}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$text->t('Close')}</button>
                    <button type="button" class="btn btn-primary" id="acmeConfirmUpgradeDomains">{$text->t('acmeConfirmUpgrade')}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="acmeRemoveDomainModal" tabindex="-1" role="dialog" aria-labelledby="acmeRemoveDomainModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeRemoveDomainModalLabel">{$text->t('acmeRemoveDomain')}</h4>
                </div>
                <div class="modal-body">
                    {$text->t('acmeRemoveDomainConfirm')} <strong id="acmeRemoveDomainName"></strong>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$text->t('Close')}</button>
                    <button type="button" class="btn btn-danger" id="acmeConfirmRemoveDomain">{$text->t('acmeRemoveDomain')}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="acmeAddDomainModal" tabindex="-1" role="dialog" aria-labelledby="acmeAddDomainModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="text-align: left;">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="acmeAddDomainModalLabel">{$text->t('acmeAddDomainButton')}</h4>
                </div>
                <div class="modal-body">
                    <div id="acmeAddDomainError" class="alert alert-danger" style="display:none;"></div>
                    <div class="alert alert-info">
                        {$text->t('acmeAvailableSlots')}
                        {$text->t('acmeSingleDomainsLabel')}: {$availableSingleSlots|intval}
                        {if $allowWildcard}
                            | {$text->t('acmeWildcardDomainsLabel')}: {$availableWildcardSlots|intval}
                        {/if}
                    </div>
                    {if $availableSingleSlots > 0}
                        <div class="form-group">
                            <label for="acmeAddSingleDomainsInput">{$text->t('acmeSingleDomainsLabel')} ({$availableSingleSlots|intval})</label>
                            <textarea id="acmeAddSingleDomainsInput" rows="4" class="form-control"></textarea>
                        </div>
                    {/if}
                    {if $allowWildcard && $availableWildcardSlots > 0}
                        <div class="form-group">
                            <label for="acmeAddWildcardDomainsInput">{$text->t('acmeWildcardDomainsLabel')} ({$availableWildcardSlots|intval})</label>
                            <textarea id="acmeAddWildcardDomainsInput" rows="4" class="form-control"></textarea>
                        </div>
                    {/if}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$text->t('Close')}</button>
                    <button type="button" class="btn btn-primary" id="acmeConfirmAddDomain">{$text->t('acmeAddDomainButton')}</button>
                </div>
            </div>
        </div>
    </div>

    </div>

<span id="acmeTexts" hidden data-error="{$text->t('anErrorOccurred')}" data-done="{$text->t('acmeDone')}" data-cancel-confirm="{$text->t('acmeCancelConfirm')}"></span>
<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mod-module .validate-ssl-unified').remove();
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
    });

        $(document).ready(function () {
            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';
            var acmeTexts = $('#acmeTexts');
            var selectedDomainToRemove = null;

            function runAction(action, data, options) {
                options = options || {};
                data = data || {};
                data['mod-action'] = action;
                data['token'] = '{$csrfToken}';
                $.post(serviceUrl, data, function (ret) {
                    // the JSON answer comes with an application/json header, so jQuery may already have parsed it
                    var payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace("<JSONRESPONSE#", "").replace("#ENDJSONRESPONSE>", ""));
                    if (payload.result === 'error') {
                        var errorMessage = payload.error || acmeTexts.data('error');
                        if (typeof options.onError === 'function') {
                            options.onError(errorMessage, payload);
                        } else {
                            $('#ModAlerts').alerts('error', errorMessage);
                        }
                        return;
                    }
                    var msg = (payload.data && payload.data.message) ? payload.data.message : acmeTexts.data('done');
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess(payload);
                    }
                    $('#ModAlerts').alerts('success', msg);
                    window.setTimeout(function () { window.location.reload(); }, 1200);
                });
            }

            $(document).on('click', '.acme-action', function (e) {
                var action = $(this).data('action');
                var domain = $(this).data('domain');

                if (action === 'createSubscription') {
                    var domainsInput = $('#acmeDomainsInput').val();
                    var domainType = $('#acmeDomainType').val();
                    runAction('createSubscription', { domains: domainsInput, domain_type: domainType });
                    return;
                }
                if (action === 'addDomain') {
                    $('#acmeAddDomainError').hide().text('');
                    $('#acmeAddDomainModal').modal('show');
                    return;
                }
                if (action === 'checkCertificateDetails') {
                    runAction('checkCertificateDetails', {});
                    return;
                }
                if (action === 'removeDomain') {
                    selectedDomainToRemove = domain || null;
                    $('#acmeRemoveDomainName').text(selectedDomainToRemove || '');
                    $('#acmeRemoveDomainModal').modal('show');
                    return;
                }
                if (action === 'cancelSubscription') {
                    if (confirm(acmeTexts.data('cancelConfirm'))) {
                        runAction('cancelSubscription', {});
                    }
                    return;
                }
                if (action === 'stopAutoRenewal') {
                    runAction('stopAutoRenewal', {});
                }
                if (action === 'openUpgradeDomains') {
                    $('#acmeUpgradeDomainsError').hide().text('');
                    $('#acmeUpgradeDomainsModal').modal('show');
                }
            });

            $(document).on('click', '#acmeConfirmRemoveDomain', function (e) {
                if (!selectedDomainToRemove) {
                    return;
                }
                $('#acmeRemoveDomainModal').modal('hide');
                runAction('removeDomain', { domain: selectedDomainToRemove });
            });
            $(document).on('click', '#acmeConfirmUpgradeDomains', function (e) {
                var newSingle   = parseInt($('#acmeUpgradeSingleInput').val(), 10);
                var newWildcard = $('#acmeUpgradeWildcardInput').length ? parseInt($('#acmeUpgradeWildcardInput').val(), 10) : -1;
                var payload = {};
                if (!isNaN(newSingle))   { payload['new_sans_count']          = newSingle; }
                if (!isNaN(newWildcard) && newWildcard >= 0) { payload['new_sans_wildcard_count'] = newWildcard; }
                runAction('upgradeConfigOptions', payload, {
                    onError: function (errorMessage) {
                        $('#acmeUpgradeDomainsError').text(errorMessage).show();
                    },
                    onSuccess: function () {
                        $('#acmeUpgradeDomainsError').hide().text('');
                        $('#acmeUpgradeDomainsModal').modal('hide');
                    }
                });
            });

            $(document).on('click', '#acmeConfirmAddDomain', function (e) {
                var singleDomainsInput = $('#acmeAddSingleDomainsInput').length ? $('#acmeAddSingleDomainsInput').val() : '';
                var wildcardDomainsInput = $('#acmeAddWildcardDomainsInput').length ? $('#acmeAddWildcardDomainsInput').val() : '';
                runAction('buyMoreDomains', {
                    single_domains: singleDomainsInput,
                    wildcard_domains: wildcardDomainsInput
                }, {
                    onError: function (errorMessage) {
                        $('#acmeAddDomainError').text(errorMessage).show();
                    },
                    onSuccess: function () {
                        $('#acmeAddDomainError').hide().text('');
                        $('#acmeAddDomainModal').modal('hide');
                    }
                });
            });

            $(document).on('click', '#Primary_Sidebar-Service_Details_Actions-Upgrade_Downgrade_Options', function (e) {
                e.preventDefault();
                $('#acmeUpgradeDomainsModal').modal('show');
            });
        });
    </script>
{/if}
