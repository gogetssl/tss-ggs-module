<script type="text/javascript" src="{$assetsURL}/js/client.js"></script>

{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$text->t('Awaiting Configuration')}</span>
                <h3>{$text->t('acmeSubscriptionConfigurationTitle')}</h3>
                <p class="tssggs-hero__domain">{$text->t('acmeSubscriptionConfigurationInfo')}</p>
            </div>
        </div>
    </section>

    <section class="tssggs-card">
        <h4 class="tssggs-card__title">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
            {$text->t('acmeAddNewDomainsTitle')}
        </h4>
        <div class="tssggs-form">
            <p class="tssggs-note tssggs-note--plain">{$text->t('acmeAddNewDomainsDescription')}</p>

            <div class="tssggs-field">
                <label for="acmeSingleDomainsInput">{$text->t('acmeSingleDomainsLabel')} ({$singleDomainsLimit|escape})</label>
                <textarea id="acmeSingleDomainsInput" rows="5" class="form-control"></textarea>
            </div>
            {if $allowWildcard}
                <div class="tssggs-field">
                    <label for="acmeWildcardDomainsInput">{$text->t('acmeWildcardDomainsLabel')} ({$wildcardDomainsLimit|escape})</label>
                    <textarea id="acmeWildcardDomainsInput" rows="5" class="form-control"></textarea>
                </div>
            {/if}
            <p class="tssggs-note">{$text->t('acmeDomainsHint')}</p>
        </div>
    </section>

    <div class="tssggs-actions tssggs-actions--end">
        <button type="button" class="tssggs-btn tssggs-btn--primary" id="acmeSubmitConfiguration">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
            {$text->t('acmeSubmitConfiguration')}
        </button>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mod-module .validate-ssl-unified').remove();
        // the rest of the page and the messages go along (main.tpl)
        modPageRestToOverview();
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
    });
</script>

    <script type="text/javascript">
        $(document).ready(function () {
            var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';

            $(document).on('click', '#acmeSubmitConfiguration', function (e) {
                var singleDomainsInput = $('#acmeSingleDomainsInput').val();
                var wildcardDomainsInput = $('#acmeWildcardDomainsInput').length ? $('#acmeWildcardDomainsInput').val() : '';

                $.post(serviceUrl, {
                    'mod-action': 'createSubscription',
                    'token': '{$csrfToken}',
                    single_domains: singleDomainsInput,
                    wildcard_domains: wildcardDomainsInput
                }, function (ret) {
                    // the JSON answer comes with an application/json header, so jQuery may already have parsed it
                    var payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace("<JSONRESPONSE#", "").replace("#ENDJSONRESPONSE>", ""));
                    if (payload.result === 'error') {
                        $('#ModAlerts').alerts('error', payload.error || '{$text->t('anErrorOccurred')}');
                        return;
                    }
                    var msg = (payload.data && payload.data.message) ? payload.data.message : '{$text->t('acmeConfigurationDone')}';
                    $('#ModAlerts').alerts('success', msg);
                    window.setTimeout(function () { window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}'; }, 1200);
                });
            });
        });
    </script>
{/if}
