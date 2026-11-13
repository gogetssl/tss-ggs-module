<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$MGLANG->T('Awaiting Configuration')}</span>
                <h3>{$MGLANG->T('aisConfigurationTitle')}</h3>
            </div>
        </div>
    </section>

    <div id="ais-alerts"></div>

    <section class="tssggs-card">
        <h4 class="tssggs-card__title">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
            {$MGLANG->T('aisDomainLabel')}
        </h4>
        <div class="tssggs-form">
            <div class="tssggs-field">
                <label for="ais-domain">{$MGLANG->T('aisDomainLabel')} <span class="tssggs-req">*</span></label>
                <input type="text" id="ais-domain" class="form-control" placeholder="example.com" value="{$serviceDomain|escape:'htmlall'}">
                <small>{$MGLANG->T('aisDomainHint')}</small>
            </div>
            {if $aisCanSwitch}
                <div class="tssggs-field">
                    <label for="ais-install-method">{$MGLANG->T('aisInstallMethod')}</label>
                    <select id="ais-install-method" class="form-control">
                        <option value="autoinstall">{$MGLANG->T('aisMethod_autoinstall')}</option>
                        <option value="acme">{$MGLANG->T('aisMethod_acme')}</option>
                    </select>
                    <small>{$MGLANG->T('aisInstallMethodHint')}</small>
                </div>
            {/if}
        </div>
    </section>

    <div class="tssggs-actions tssggs-actions--end">
        <button id="ais-submit" type="button" class="tssggs-btn tssggs-btn--primary">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
            {$MGLANG->T('aisSubmitConfiguration')}
        </button>
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
</script>

<script type="text/javascript">
    $(document).ready(function () {
        var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';

        $('#ais-submit').on('click', function () {
            var domain = $('#ais-domain').val().trim();
            var btn    = $(this);

            $('#ais-alerts').html('');

            if (!domain) {
                $('#MGAlerts').alerts('error', '{$MGLANG->T('aisDomainRequired')}');
                return;
            }

            btn.prop('disabled', true).text('...');

            $.post(serviceUrl, {ldelim}'mg-action': 'createAis', token: '{$csrfToken}', domain: domain, install_method: $('#ais-install-method').val() || 'autoinstall'{rdelim}, function (ret) {
                // the JSON answer comes with an application/json header, so jQuery may already have parsed it
                var payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
                if (payload.result === 'error') {
                    $('#MGAlerts').alerts('error', payload.error || 'An error occurred.');
                    btn.prop('disabled', false).text('{$MGLANG->T('aisSubmitConfiguration')}');
                } else {
                    var msg = (payload.data && payload.data.message) ? payload.data.message : '{$MGLANG->T('aisCreatedSuccessfully')}';
                    $('#MGAlerts').alerts('success', msg);
                    window.setTimeout(function () {
                        window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
                    }, 1200);
                }
            });
        });
    });
</script>
{/if}
