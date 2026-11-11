{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <div id="tssggs-smime-alerts">
        {if $refreshError}
            <div class="alert alert-warning" role="alert">{$MGLANG->T('smimeRefreshFailed')} {$refreshError|escape}</div>
        {/if}
    </div>

    <section class="tssggs-card tssggs-hero tssggs-hero--{$smimeTone}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                {if $smimeTone == 'ok'}
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                {else}
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                {/if}
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--{$smimeTone}">{$smimeStatusText|escape}</span>
                <h3>{$productName|escape}</h3>
                <p class="tssggs-hero__domain">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/></svg>
                    {$smimeSubject|escape}
                </p>
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <p>
                {if $smimeStatus == 'active'}{$MGLANG->T('smimeInfoActive')}
                {elseif $smimeStatus == 'pending' || $smimeStatus == 'processing'}{if $smimeAllApproved}{$MGLANG->T('smimeInfoApproved')}{else}{$MGLANG->T('smimeInfoPending')}{/if}
                {elseif $smimeTone == 'off'}{$MGLANG->T('smimeInfoClosed')}
                {else}{$MGLANG->T('smimeInfoContactSupport')}{/if}
            </p>
            <div class="tssggs-actions">
                <button id="tssggs-smime-refresh" type="button" class="tssggs-btn{if $smimeTone == 'wait'} tssggs-btn--primary{/if}">
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
                <dt>{$MGLANG->T('smimeEmails')}</dt>
                <dd>
                    <ul class="tssggs-emails">
                        {foreach from=$smimeEmails item=entry name=emails}
                            <li>
                                <span class="tssggs-mono">{$entry.email|escape}</span>
                                {if $smimeStatus == 'active' || $entry.approved}
                                    <span class="tssggs-chip tssggs-chip--ok">{$MGLANG->T('smimeEmailValidated')}</span>
                                {elseif $smimeTone == 'wait'}
                                    <span class="tssggs-chip">{$MGLANG->T('smimeEmailAwaitingValidation')}</span>
                                {/if}
                            </li>
                        {foreachelse}
                            <li>{$MGLANG->T('validation_page', 'NA')}</li>
                        {/foreach}
                    </ul>
                </dd>
                <dt>{$MGLANG->T('validation_page', 'ValidFrom')}</dt><dd>{if $smimeValidFrom}{$smimeValidFrom}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidTill')}</dt><dd>{if $smimeValidTill}{$smimeValidTill}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('smimeOrderedAt')}</dt><dd>{if $smimeOrderedAt}{$smimeOrderedAt}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$smimeItemId|escape}</dd>
                <dt>{$MGLANG->T('validation_page', 'VendorOrderID')}</dt><dd class="tssggs-mono">{if $smimeVendorOrderId}{$smimeVendorOrderId|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
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
            <p class="tssggs-note tssggs-note--card">{if $smimeCsrProvided}{$MGLANG->T('smimeCollectCsr')}{else}{$MGLANG->T('smimeCollectEmail')}{/if}</p>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mg-module .validate-ssl-unified').remove();
    });

    $(document).on('click', '#tssggs-smime-refresh', function () {
        var btn = $(this), alerts = $('#tssggs-smime-alerts');
        btn.prop('disabled', true);
        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', {ldelim}'mg-action': 'refreshSmimeDetails', token: '{$csrfToken}'{rdelim}, function (ret) {
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
