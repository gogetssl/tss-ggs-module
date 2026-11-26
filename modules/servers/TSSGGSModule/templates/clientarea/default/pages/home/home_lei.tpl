{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <div id="tssggs-lei-alerts">
        {if $flashSuccess}
            <div class="alert alert-success" role="alert">{$flashSuccess}</div>
        {/if}
        {if $flashError}
            <div class="alert alert-danger" role="alert">{$flashError|escape}</div>
        {/if}
        {if $refreshError}
            <div class="alert alert-warning" role="alert">{$MODLANG->T('smimeRefreshFailed')} {$refreshError|escape}</div>
        {/if}
    </div>

    <section class="tssggs-card tssggs-hero tssggs-hero--{$leiTone}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--{$leiTone}">{$leiStatusText|escape}</span>
                <h3>{$productName|escape}</h3>
                {if $leiLegalName}
                    <p class="tssggs-hero__domain">{$leiLegalName|escape}</p>
                {/if}
                {if $leiNumber}
                    <p class="tssggs-hero__domain tssggs-mono">LEI {$leiNumber|escape}</p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <p>
                {if $leiStatus == 'active'}{if $leiIsRenewal}{$MODLANG->T('leiInfoRenewed')}{else}{$MODLANG->T('leiInfoActive')}{/if}
                {elseif $leiStatus == 'incomplete'}{$MODLANG->T('leiInfoIncomplete')}
                {elseif $leiStatus == 'need_action'}{$MODLANG->T('leiInfoNeedAction')}
                {elseif $leiStatus == 'user_confirmed'}{$MODLANG->T('leiInfoUserConfirmed')}
                {elseif $leiStatus == 'pending' || $leiStatus == 'pre_issue'}{$MODLANG->T('leiInfoPending')}
                {elseif $leiStatus == 'failed'}{$MODLANG->T('leiInfoFailed')}
                {elseif $leiStatus == 'revoked'}{$MODLANG->T('leiInfoRevoked')}
                {elseif $leiStatus == 'unpaid'}{$MODLANG->T('smimeInfoContactSupport')}
                {elseif $leiStatus == 'expired'}{$MODLANG->T('leiInfoExpired')}
                {elseif $leiTone == 'wait'}{if $leiIsRenewal}{$MODLANG->T('leiInfoRenewalProcessing')}{else}{$MODLANG->T('leiInfoProcessing')}{/if}
                {elseif $leiTone == 'off'}{$MODLANG->T('smimeInfoClosed')}
                {else}{$MODLANG->T('smimeInfoContactSupport')}{/if}
            </p>
            <div class="tssggs-actions">
                {if $leiNeedsConfirm}
                    <button id="tssggs-lei-confirm" type="button" class="tssggs-btn tssggs-btn--primary">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{$MODLANG->T('leiConfirmButton')}</span>
                    </button>
                {/if}
                {if $leiGleifUrl}
                    <a class="tssggs-btn" href="{$leiGleifUrl|escape}" target="_blank" rel="noopener">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        {$MODLANG->T('leiGleifButton')}
                    </a>
                {/if}
                {if $renewOpen}
                    <button type="button" class="tssggs-btn tssggs-btn--primary" data-toggle="modal" data-target="#tssggsRenewModal">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                        {$MODLANG->T('renewButton')}
                    </button>
                {/if}
                <button id="tssggs-lei-refresh" type="button" class="tssggs-btn{if $leiTone == 'wait'} tssggs-btn--primary{/if}">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    <span>{$MODLANG->T('smimeRefreshDetails')}</span>
                </button>
            </div>
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MODLANG->T('leiCardEntity')}
            </h4>
            {if $leiNeedsConfirm}
                <p>{$MODLANG->T('leiConfirmHint')}</p>
            {/if}
            <dl class="tssggs-dl">
                <dt>{$MODLANG->T('leiNumberLabel')}</dt><dd class="tssggs-mono">{if $leiNumber}{$leiNumber|escape}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('leiField_legal_name')}</dt><dd>{if $leiLegalName}{$leiLegalName|escape}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('leiField_registration_authority_entity_id')}</dt><dd>{if $leiRegistration}{$leiRegistration|escape}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('leiField_legal_jurisdiction')}</dt><dd>{if $leiJurisdiction}{$leiJurisdiction|escape}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('leiLegalAddress')}</dt><dd>{if $leiLegalAddress}{$leiLegalAddress|escape}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                {if $leiHqAddress && $leiHqAddress != $leiLegalAddress}<dt>{$MODLANG->T('leiHqAddress')}</dt><dd>{$leiHqAddress|escape}</dd>{/if}
                <dt>{$MODLANG->T('leiSigningSection')}</dt><dd>{if $leiSigner}{$leiSigner|escape}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                {if $leiRaCode}<dt>{$MODLANG->T('leiRaCode')}</dt><dd class="tssggs-mono">{$leiRaCode|escape}</dd>{/if}
            </dl>
        </section>

        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                {$MODLANG->T('validation_page', 'cardBilling')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MODLANG->T('leiValidFrom')}</dt><dd>{if $leiValidFrom}{$leiValidFrom}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('leiValidTill')}</dt><dd>{if $leiValidTill}{$leiValidTill}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('smimeOrderedAt')}</dt><dd>{if $leiOrderedAt}{$leiOrderedAt}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('smimeNextDue')}</dt><dd>{if $billing.nextDue}{$billing.nextDue}{else}{$MODLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MODLANG->T('validation_page', 'ReccuringAmount')}</dt><dd>{$billing.amount}</dd>
                <dt>{$MODLANG->T('validation_page', 'BillingCycle')}</dt><dd>{$billing.cycle|escape}</dd>
                <dt>{$MODLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$leiItemId|escape}</dd>
            </dl>
        </section>
    </div>
    {if $renewOpen}
    <div class="modal fade" id="tssggsRenewModal" tabindex="-1" role="dialog" aria-labelledby="tssggsRenewLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                    <input type="hidden" name="mod-action" value="renewOrder">
                    <input type="hidden" name="token" value="{$csrfToken}">
                    <div class="modal-header" style="display:block;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="tssggsRenewLabel" style="text-align:left;">{$MODLANG->T('renewTitle')}</h4>
                    </div>
                    <div class="modal-body tssggs-cert" style="text-align:left;">
                        <p>{$MODLANG->T('leiRenewIntro')}</p>
                        {if $renewSame}
                            <label class="tssggs-check">
                                <input type="radio" name="renew_same" value="1" checked>
                                <span><strong>{$MODLANG->T('renewSame')}</strong><br><small>{$MODLANG->T('leiRenewSameHint')}</small></span>
                            </label>
                            <label class="tssggs-check" style="margin-top:12px;">
                                <input type="radio" name="renew_same" value="0">
                                <span><strong>{$MODLANG->T('renewNew')}</strong><br><small>{$MODLANG->T('leiRenewNewHint')}</small></span>
                            </label>
                        {else}
                            <input type="hidden" name="renew_same" value="0">
                            <p class="tssggs-note tssggs-note--plain">{$MODLANG->T('leiRenewNewHint')}</p>
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{$MODLANG->T('validation_page', 'Close')}</button>
                        <button type="submit" class="btn btn-success">{$MODLANG->T('renewCreateInvoice')}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {/if}
</div>

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

    $(document).on('click', '#tssggs-lei-confirm', function () {
        var btn = $(this), alerts = $('#tssggs-lei-alerts');
        if (!window.confirm('{$MODLANG->T('leiConfirmAsk')|escape:'javascript'}')) {
            return;
        }
        btn.prop('disabled', true);
        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', {ldelim}'mod-action': 'confirmLei', token: '{$csrfToken}'{rdelim}, function (ret) {
            var payload;
            try {
                payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
            } catch (e) {
                payload = {ldelim}result: 'error'{rdelim};
            }
            if (payload.result === 'error') {
                alerts.html($('<div class="alert alert-danger" role="alert"></div>').text(payload.error || '{$MODLANG->T('smimeErrorOccurred')|escape:'javascript'}'));
                btn.prop('disabled', false);
                return;
            }
            window.location.reload();
        }).fail(function () {
            alerts.html($('<div class="alert alert-danger" role="alert"></div>').text('{$MODLANG->T('smimeErrorOccurred')|escape:'javascript'}'));
            btn.prop('disabled', false);
        });
    });
    $(document).on('click', '#tssggs-lei-refresh', function () {
        var btn = $(this), alerts = $('#tssggs-lei-alerts');
        btn.prop('disabled', true);
        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', {ldelim}'mod-action': 'refreshLeiDetails', token: '{$csrfToken}'{rdelim}, function (ret) {
            var payload;
            try {
                payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
            } catch (e) {
                payload = {ldelim}result: 'error'{rdelim};
            }
            if (payload.result === 'error') {
                alerts.html($('<div class="alert alert-danger" role="alert"></div>').html(payload.error || '{$MODLANG->T('smimeErrorOccurred')|escape:'javascript'}'));
                btn.prop('disabled', false);
                return;
            }
            window.location.reload();
        }).fail(function () {
            alerts.html($('<div class="alert alert-danger" role="alert"></div>').text('{$MODLANG->T('smimeErrorOccurred')|escape:'javascript'}'));
            btn.prop('disabled', false);
        });
    });
</script>
{/if}
