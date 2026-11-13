<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert">

    {if $refreshError}
        <div class="alert alert-warning" role="alert">{$refreshError|escape}</div>
    {/if}

    {* ---------- Hero: licence state and the way into the CodeGuard dashboard ---------- *}
    <section class="tssggs-card tssggs-hero tssggs-hero--{if $codeGuardOrdered}{$codeGuardTone}{else}wait{/if}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>
            </span>
            <div class="tssggs-hero__title">
                {if $codeGuardOrdered}
                    <span class="tssggs-badge tssggs-badge--{$codeGuardTone}">{$codeGuardStatus|capitalize|escape}</span>
                {else}
                    <span class="tssggs-badge tssggs-badge--wait">{$MGLANG->T('codeGuardNotOrdered')}</span>
                {/if}
                <h3>{$productName|escape}</h3>
                {if $codeGuardContact}
                    <p class="tssggs-hero__domain">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        {$codeGuardContact.email|escape}
                    </p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            {if !$codeGuardOrdered}<p>{$MGLANG->T('codeGuardOrderFailedHint')}</p>{/if}
            <div class="tssggs-actions">
                {if $codeGuardUsable}
                    <button id="codeguard-dashboard" type="button" class="tssggs-btn tssggs-btn--primary">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        {$MGLANG->T('codeGuardOpenDashboard')}
                    </button>
                    <button id="codeguard-refresh" type="button" class="tssggs-btn">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                        {$MGLANG->T('codeGuardRefreshDetails')}
                    </button>
                    <button id="codeguard-edit-contact" type="button" class="tssggs-btn">{$MGLANG->T('codeGuardChangeContact')}</button>
                {elseif !$codeGuardOrdered}
                    <button id="codeguard-order" type="button" class="tssggs-btn tssggs-btn--primary">{$MGLANG->T('codeGuardOrderNow')}</button>
                {/if}
            </div>
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MGLANG->T('codeGuardStatus')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('codeGuardProductName')}</dt><dd>{$productName|escape}</dd>
                {if $codeGuardStart && $codeGuardEnd}
                    <dt>{$MGLANG->T('codeGuardPeriod')}</dt><dd>{$codeGuardStart|escape} &ndash; {$codeGuardEnd|escape}</dd>
                {/if}
                {if $codeGuardContact}
                    <dt>{$MGLANG->T('codeGuardContact')}</dt>
                    <dd>{$codeGuardContact.first_name|escape} {$codeGuardContact.last_name|escape} &lt;{$codeGuardContact.email|escape}&gt;</dd>
                {/if}
                {if $codeGuardLicenseId}
                    <dt>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$codeGuardLicenseId|escape}</dd>
                {/if}
                {if $codeGuardVendorId}
                    <dt>{$MGLANG->T('validation_page', 'VendorOrderID')}</dt><dd class="tssggs-mono">{$codeGuardVendorId|escape}</dd>
                {/if}
            </dl>
        </section>

        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                {$MGLANG->T('validation_page', 'cardBilling')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('validation_page', 'RegistrationDate')}</dt><dd>{if $billing.registration}{$billing.registration}{else}-{/if}</dd>
                <dt>{$MGLANG->T('smimeNextDue')}</dt><dd>{if $billing.nextDue}{$billing.nextDue}{else}-{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ReccuringAmount')}</dt><dd>{$billing.amount}</dd>
                <dt>{$MGLANG->T('validation_page', 'BillingCycle')}</dt><dd>{$billing.cycle|escape}</dd>
            </dl>
        </section>
    </div>

    {if $codeGuardLicense}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                {$MGLANG->T('codeGuardStatus')}
            </h4>
            <dl class="tssggs-dl">
                {foreach from=$codeGuardLicense item=row}
                    {* the label comes from the language file, which Lang has escaped already *}
                    <dt>{$row.label}</dt><dd>{$row.value|escape}</dd>
                {/foreach}
            </dl>
        </section>
    {/if}
</div>

<div class="modal fade" id="codeGuardContactModal" tabindex="-1" role="dialog" aria-labelledby="codeGuardContactModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="text-align:left;">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="codeGuardContactModalLabel">{$MGLANG->T('codeGuardChangeContact')}</h4>
            </div>
            <div class="modal-body">
                <p>{$MGLANG->T('codeGuardContactHint')}</p>
                <div class="form-group">
                    <label for="cg_first_name">{$MGLANG->T('codeGuardFirstName')}</label>
                    <input type="text" class="form-control" id="cg_first_name" value="{if $codeGuardContact}{$codeGuardContact.first_name|escape}{/if}">
                </div>
                <div class="form-group">
                    <label for="cg_last_name">{$MGLANG->T('codeGuardLastName')}</label>
                    <input type="text" class="form-control" id="cg_last_name" value="{if $codeGuardContact}{$codeGuardContact.last_name|escape}{/if}">
                </div>
                <div class="form-group">
                    <label for="cg_email">{$MGLANG->T('codeGuardEmail')}</label>
                    <input type="email" class="form-control" id="cg_email" value="{if $codeGuardContact}{$codeGuardContact.email|escape}{/if}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                <button type="button" class="btn btn-primary" id="codeGuardConfirmContact">{$MGLANG->T('codeGuardSaveContact')}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mg-module .validate-ssl-unified').remove();
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
                    $('#MGAlerts').alerts('error', payload.error || 'Error');
                    return;
                }
                var msg = (payload.data && payload.data.message) ? payload.data.message : 'Done';
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(payload);
                    return;
                }
                $('#MGAlerts').alerts('success', msg);
                window.setTimeout(function () { window.location.reload(); }, 1200);
            });
        }

        $('body').on('click', '#codeguard-dashboard', function () {
            var button = $(this);
            button.prop('disabled', true);
            // the link is one-time and is fetched on every click, so it is opened as soon as it arrives
            runAction('openCodeGuardDashboard', {}, {
                onSuccess: function (payload) {
                    button.prop('disabled', false);
                    var link = payload.data ? payload.data.link : '';
                    if (link) {
                        window.open(link, '_blank', 'noopener');
                    } else {
                        $('#MGAlerts').alerts('error', 'No dashboard link was returned');
                    }
                }
            });
        });

        $('body').on('click', '#codeguard-refresh', function () {
            runAction('refreshCodeGuardDetails', {});
        });

        $('body').on('click', '#codeguard-order', function () {
            runAction('createCodeGuard', {});
        });

        $('body').on('click', '#codeguard-edit-contact', function () {
            $('#codeGuardContactModal').modal('show');
        });

        $('body').on('click', '#codeGuardConfirmContact', function () {
            $('#codeGuardContactModal').modal('hide');
            runAction('updateCodeGuardContact', {
                cg_first_name: $('#cg_first_name').val(),
                cg_last_name: $('#cg_last_name').val(),
                cg_email: $('#cg_email').val()
            });
        });
    });
</script>
{/if}
