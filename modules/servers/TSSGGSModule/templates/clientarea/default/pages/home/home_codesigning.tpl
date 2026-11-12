{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <div id="tssggs-cs-alerts">
        {if $flashSuccess}
            <div class="alert alert-success" role="alert">{$flashSuccess}</div>
        {/if}
        {if $flashError}
            <div class="alert alert-danger" role="alert">{$flashError|escape}</div>
        {/if}
        {if $refreshError}
            <div class="alert alert-warning" role="alert">{$MGLANG->T('smimeRefreshFailed')} {$refreshError|escape}</div>
        {/if}
    </div>

    <section class="tssggs-card tssggs-hero tssggs-hero--{$csTone}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="m18 16 4-4-4-4M6 8l-4 4 4 4M14.5 4l-5 16"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--{$csTone}">{$csStatusText|escape}</span>
                <h3>{$productName|escape}</h3>
                {if $csOrganization}
                    <p class="tssggs-hero__domain">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                        {$csOrganization|escape}
                    </p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <p>
                {if $csStatus == 'active'}
                    {if $csMethod == 'ship-token'}{$MGLANG->T('csInfoActiveShipToken')}{elseif $csMethod == 'existing-token'}{$MGLANG->T('csInfoActiveExistingToken')}{else}{$MGLANG->T('csInfoActiveHsm')}{/if}
                {elseif $csTone == 'wait'}{$MGLANG->T('csInfoPending')}
                {elseif $csTone == 'off'}{$MGLANG->T('smimeInfoClosed')}
                {else}{$MGLANG->T('smimeInfoContactSupport')}{/if}
            </p>
            <div class="tssggs-actions">
                {if $renewOpen}
                    <button type="button" class="tssggs-btn tssggs-btn--primary" data-toggle="modal" data-target="#tssggsRenewModal">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                        {$MGLANG->T('renewButton')}
                    </button>
                {/if}
                <button id="tssggs-cs-refresh" type="button" class="tssggs-btn{if $csTone == 'wait'} tssggs-btn--primary{/if}">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    <span>{$MGLANG->T('smimeRefreshDetails')}</span>
                </button>
            </div>
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MGLANG->T('validation_page', 'cardCertificate')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('csOrganization')}</dt><dd>{if $csOrganization}{$csOrganization|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('csDelivery')}</dt><dd>{if $csDelivery}{$csDelivery|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidFrom')}</dt><dd>{if $csValidFrom}{$csValidFrom}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidTill')}</dt><dd>{if $csValidTill}{$csValidTill}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('smimeOrderedAt')}</dt><dd>{if $csOrderedAt}{$csOrderedAt}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$csItemId|escape}</dd>
                <dt>{$MGLANG->T('validation_page', 'VendorOrderID')}</dt><dd class="tssggs-mono">{if $csVendorOrderId}{$csVendorOrderId|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
            </dl>
        </section>

        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                {$MGLANG->T('validation_page', 'cardBilling')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('validation_page', 'RegistrationDate')}</dt><dd>{if $billing.registration}{$billing.registration}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('smimeNextDue')}</dt><dd>{if $billing.nextDue}{$billing.nextDue}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ReccuringAmount')}</dt><dd>{$billing.amount}</dd>
                <dt>{$MGLANG->T('validation_page', 'BillingCycle')}</dt><dd>{$billing.cycle|escape}</dd>
            </dl>
        </section>
    </div>
    {if $renewOpen}
    <div class="modal fade" id="tssggsRenewModal" tabindex="-1" role="dialog" aria-labelledby="tssggsRenewLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}">
                    <input type="hidden" name="mg-action" value="renewOrder">
                    <input type="hidden" name="token" value="{$csrfToken}">
                    <div class="modal-header" style="display:block;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="tssggsRenewLabel" style="text-align:left;">{$MGLANG->T('renewTitle')}</h4>
                    </div>
                    <div class="modal-body tssggs-cert" style="text-align:left;">
                        <p>{$MGLANG->T('renewIntro')}</p>
                        {if $renewSame}
                            <label class="tssggs-check">
                                <input type="radio" name="renew_same" value="1" checked data-tssggs-renew>
                                <span><strong>{$MGLANG->T('renewSame')}</strong><br><small>{if $renewIsCs}{$MGLANG->T('renewSameHintCs')}{else}{$MGLANG->T('renewSameHintSmime')}{/if}</small></span>
                            </label>
                            {if $renewNeedsModel}
                                <div class="tssggs-field" data-tssggs-renew-same style="margin:8px 0 0 24px;">
                                    <label for="tssggs-renew-model">{$MGLANG->T('renewModelLabel')} <span class="tssggs-req">*</span></label>
                                    <select id="tssggs-renew-model" name="renew_model" class="form-control" required>
                                        <option value="">-</option>
                                        {foreach from=$renewModels key=code item=name}<option value="{$code|escape:'htmlall'}">{$name|escape}</option>{/foreach}
                                    </select>
                                </div>
                            {/if}
                            {if $renewAgreement}
                                <label class="tssggs-check" data-tssggs-renew-same style="margin:8px 0 0 24px;">
                                    <input type="checkbox" name="agreement" value="1" required>
                                    <span>{$MGLANG->T('sslConfigAgreement')} {if $renewAgreement.url}<a href="{$renewAgreement.url|escape}" target="_blank" rel="noopener">{$renewAgreement.name|escape}</a>{else}{$renewAgreement.name|escape}{/if}</span>
                                </label>
                            {/if}
                            <label class="tssggs-check" style="margin-top:12px;">
                                <input type="radio" name="renew_same" value="0" data-tssggs-renew>
                                <span><strong>{$MGLANG->T('renewNew')}</strong><br><small>{$MGLANG->T('renewNewHint')}</small></span>
                            </label>
                        {else}
                            <input type="hidden" name="renew_same" value="0">
                            <p class="tssggs-note tssggs-note--plain">{if $renewIsCs}{$MGLANG->T('renewNoSameCs')}{else}{$MGLANG->T('renewNewHint')}{/if}</p>
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
                        <button type="submit" class="btn btn-success">{$MGLANG->T('renewCreateInvoice')}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {/if}
</div>

<script type="text/javascript">
    // Renew dialog: the token model and agreement belong to "same details"
    $(document).on('change', '[data-tssggs-renew]', function () {
        var same = $(this).closest('form').find('[data-tssggs-renew]:checked').val() === '1';
        $(this).closest('form').find('[data-tssggs-renew-same]').toggle(same).find(':input').prop('disabled', !same);
    });
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mg-module .validate-ssl-unified').remove();
    });

    $(document).on('click', '#tssggs-cs-refresh', function () {
        var btn = $(this), alerts = $('#tssggs-cs-alerts');
        btn.prop('disabled', true);
        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', {ldelim}'mg-action': 'refreshCodeSigningDetails', token: '{$csrfToken}'{rdelim}, function (ret) {
            var payload;
            try {
                payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
            } catch (e) {
                payload = {ldelim}result: 'error'{rdelim};
            }
            if (payload.result === 'error') {
                alerts.html($('<div class="alert alert-danger" role="alert"></div>').html(payload.error || '{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}'));
                btn.prop('disabled', false);
                return;
            }
            window.location.reload();
        }).fail(function () {
            alerts.html($('<div class="alert alert-danger" role="alert"></div>').text('{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}'));
            btn.prop('disabled', false);
        });
    });
</script>
{/if}
