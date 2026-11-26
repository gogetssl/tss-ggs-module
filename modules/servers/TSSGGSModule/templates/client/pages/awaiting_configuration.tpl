<div class="validate-ssl-unified tssggs-cert">

    {* ---------- Hero: the certificate waits for the client's order details ---------- *}
    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4M12 16h.01"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$text->t('awaiting_configuration', 'Ready to set up')}</span>
                <h3>{$product_name|escape}</h3>
                {if $domain}
                    <p class="tssggs-hero__domain">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
                        {$domain|escape}
                    </p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            {if $renewalNotice}<p class="tssggs-renewal-notice"><strong>{$renewalNotice|escape}</strong></p>{/if}
            {if $certificateToken}
                {* the certificate is bought: its token completes and manages the order in any plugin (IncompleteOrder) *}
                <div class="tssggs-token">
                    <p class="tssggs-token__title">{$text->t('awaiting_configuration', 'tokenTitle')}</p>
                    <p class="tssggs-token__text">{$text->t('awaiting_configuration', 'tokenText')}</p>
                    <div class="tssggs-token__row">
                        <input type="text" class="tssggs-token__value tssggs-mono" id="tssggs-token-{$serviceid}" value="{$certificateToken|escape}" readonly aria-label="{$text->t('awaiting_configuration', 'tokenTitle')|escape}">
                        <button type="button" class="tssggs-btn tssggs-token__copy" data-copy-target="tssggs-token-{$serviceid}" data-copied="{$text->t('awaiting_configuration', 'tokenCopied')|escape}">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            <span>{$text->t('awaiting_configuration', 'tokenCopy')}</span>
                        </button>
                    </div>
                </div>
            {/if}
            <p>{$text->t('awaiting_configuration', 'desc')}</p>
            <div class="tssggs-actions">
                <a class="tssggs-btn tssggs-btn--primary" href="{$url|escape}">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                    {$text->t('awaiting_configuration', 'Set Up')}
                </a>
            </div>
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$text->t('validation_page', 'cardCertificate')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$text->t('awaiting_configuration', 'Domain Name')}</dt>
                <dd>{if $domain}{$domain|escape}{else}{$text->t('awaiting_configuration', 'N/A')}{/if}</dd>
                <dt>{$text->t('awaiting_configuration', 'Status')}</dt>
                <dd><span class="tssggs-badge tssggs-badge--wait">{$text->t('awaiting_configuration', 'Ready to set up')}</span></dd>
                <dt>{$text->t('awaiting_configuration', 'order_number')}</dt>
                <dd>{if $order_number}<span class="tssggs-mono">{$order_number|escape}</span>{else}{$text->t('awaiting_configuration', 'N/A')}{/if}</dd>
                <dt>{$text->t('awaiting_configuration', 'Valid from')}</dt>
                <dd>{$text->t('awaiting_configuration', 'N/A')}</dd>
                <dt>{$text->t('awaiting_configuration', 'Valid to')}</dt>
                <dd>{$text->t('awaiting_configuration', 'N/A')}</dd>
            </dl>
        </section>

        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                {$text->t('validation_page', 'cardBilling')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$text->t('validation_page', 'RegistrationDate')}</dt>
                <dd>{if $billing.registration}{$billing.registration}{else}{$text->t('awaiting_configuration', 'N/A')}{/if}</dd>
                <dt>{$text->t('smimeNextDue')}</dt>
                <dd>{if $billing.nextDue}{$billing.nextDue}{else}{$text->t('awaiting_configuration', 'N/A')}{/if}</dd>
                <dt>{$text->t('validation_page', 'ReccuringAmount')}</dt>
                <dd>{$billing.amount}</dd>
                <dt>{$text->t('validation_page', 'BillingCycle')}</dt>
                <dd>{$billing.cycle|escape}</dd>
            </dl>
        </section>
    </div>
</div>

<script>
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mod-module .validate-ssl-unified').remove();
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
    });
    // the block above is cloned: a delegated handler survives it
    $(document).on('click', '.tssggs-token__copy', function () {
        var button = this, input = document.getElementById(button.getAttribute('data-copy-target'));
        if (!input) { return; }
        var label = button.querySelector('span'), text = label.textContent;
        var done = function () { label.textContent = button.getAttribute('data-copied'); setTimeout(function () { label.textContent = text; }, 2000); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(done, function () { input.select(); document.execCommand('copy'); done(); });
        } else {
            input.select(); document.execCommand('copy'); done();
        }
    });
    $(document).on('focus click', '.tssggs-token__value', function () { this.select(); });
</script>
