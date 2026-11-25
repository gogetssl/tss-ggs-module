{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <div id="tssggs-pci-alerts">
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

    <section class="tssggs-card tssggs-hero tssggs-hero--{$pciTone}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4M12 16h.01"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--{$pciTone}">{$pciStatusText|escape}</span>
                <h3>{$productName|escape}</h3>
                {if $pciOrganization}
                    <p class="tssggs-hero__domain">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                        {$pciOrganization|escape}
                    </p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <p>
                {if $pciStatus == 'active'}{$MGLANG->T('pciInfoActive')}
                {elseif $pciTone == 'wait'}{$MGLANG->T('pciInfoPending')}
                {elseif $pciTone == 'off'}{$MGLANG->T('smimeInfoClosed')}
                {else}{$MGLANG->T('smimeInfoContactSupport')}{/if}
            </p>
            <div class="tssggs-actions">
                {if $pciLogin}
                    <a class="tssggs-btn tssggs-btn--primary" href="{$pciLoginUrl|escape}" target="_blank" rel="noopener">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        {$MGLANG->T('pciOpenControlCentre')}
                    </a>
                {/if}
                <button id="tssggs-pci-refresh" type="button" class="tssggs-btn{if $pciTone == 'wait'} tssggs-btn--primary{/if}">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    <span>{$MGLANG->T('smimeRefreshDetails')}</span>
                </button>
            </div>
        </div>
    </section>

    {if $pciStatus == 'active'}
    <section class="tssggs-card">
        <h4 class="tssggs-card__title">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            {$MGLANG->T('pciLoginSection')}
        </h4>
        {if $pciLogin}
            <div class="tssggs-form"><p class="tssggs-note tssggs-note--plain">{$MGLANG->T('pciLoginHint')}</p></div>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('pciLoginPage')}</dt><dd><a href="{$pciLoginUrl|escape}" target="_blank" rel="noopener">{$pciLoginUrl|escape}</a></dd>
                <dt>{$MGLANG->T('pciUsername')}</dt>
                <dd><code class="tssggs-mono" id="tssggs-pci-user">{$pciLogin.username|escape}</code>
                    <button type="button" class="tssggs-btn tssggs-btn--sm" data-tssggs-copy="#tssggs-pci-user">{$MGLANG->T('pciCopy')}</button></dd>
                <dt>{$MGLANG->T('pciPassword')}</dt>
                <dd><code class="tssggs-mono" id="tssggs-pci-pass" data-secret="{$pciLogin.password|escape:'htmlall'}">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</code>
                    <button type="button" class="tssggs-btn tssggs-btn--sm" id="tssggs-pci-show">{$MGLANG->T('pciShow')}</button>
                    <button type="button" class="tssggs-btn tssggs-btn--sm" data-tssggs-copy="#tssggs-pci-pass">{$MGLANG->T('pciCopy')}</button></dd>
            </dl>
        {else}
            <div class="tssggs-form"><p class="tssggs-note tssggs-note--plain">{$MGLANG->T('pciNoLogin')}</p></div>
        {/if}
    </section>
    {/if}

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MGLANG->T('pciDetailsSection')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('dsOrganization')}</dt><dd>{if $pciOrganization}{$pciOrganization|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('pciAdministrator')}</dt><dd>{if $pciAdministrator}{$pciAdministrator|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('pciPeriod')}</dt><dd>{if $pciValidFrom || $pciValidTill}{if $pciValidFrom}{$pciValidFrom}{else}-{/if} &ndash; {if $pciValidTill}{$pciValidTill}{else}-{/if}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('smimeOrderedAt')}</dt><dd>{if $pciOrderedAt}{$pciOrderedAt}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$pciItemId|escape}</dd>
                <dt>{$MGLANG->T('validation_page', 'VendorOrderID')}</dt><dd class="tssggs-mono">{if $pciVendorOrderId}{$pciVendorOrderId|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
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

    // the password stays hidden until the client asks for it
    $(document).on('click', '#tssggs-pci-show', function () {
        var pass = $('#tssggs-pci-pass'), shown = pass.data('shown');
        pass.text(shown ? '••••••••' : pass.attr('data-secret')).data('shown', !shown);
        $(this).text(shown ? '{$MGLANG->T('pciShow')|escape:'javascript'}' : '{$MGLANG->T('pciHide')|escape:'javascript'}');
    });
    $(document).on('click', '[data-tssggs-copy]', function () {
        var el = $($(this).attr('data-tssggs-copy')), btn = $(this), text = el.attr('data-secret') || el.text();
        var done = function () {
            btn.text('{$MGLANG->T('pciCopied')|escape:'javascript'}');
            window.setTimeout(function () { btn.text('{$MGLANG->T('pciCopy')|escape:'javascript'}'); }, 1500);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
        } else {
            var area = $('<textarea style="position:fixed;opacity:0"></textarea>').val(text).appendTo('body');
            area[0].select();
            document.execCommand('copy');
            area.remove();
            done();
        }
    });

    $(document).on('click', '#tssggs-pci-refresh', function () {
        var btn = $(this), alerts = $('#tssggs-pci-alerts');
        btn.prop('disabled', true);
        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', {ldelim}'mod-action': 'refreshPciDetails', token: '{$csrfToken}'{rdelim}, function (ret) {
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
