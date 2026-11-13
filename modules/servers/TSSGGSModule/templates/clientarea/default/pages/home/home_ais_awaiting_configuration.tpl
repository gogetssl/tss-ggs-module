<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert">

    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$MGLANG->T('Awaiting Configuration')}</span>
                <h3>{$productName|escape}</h3>
                <p class="tssggs-hero__domain">{$MGLANG->T('aisDomainHint')}</p>
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <div class="tssggs-actions">
                <a class="tssggs-btn tssggs-btn--primary" href="{$configurationURL|escape:'htmlall'}">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                    {$MGLANG->T('configureNow')}
                </a>
            </div>
        </div>
    </section>

    <section class="tssggs-card">
        <h4 class="tssggs-card__title">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
            {$MGLANG->T('aisOrderStatus')}
        </h4>
        <dl class="tssggs-dl">
            <dt>{$MGLANG->T('aisProductName')}</dt><dd>{$productName|escape}</dd>
            <dt>{$MGLANG->T('configurationStatus')}</dt><dd><span class="tssggs-badge tssggs-badge--wait">{$MGLANG->T('Awaiting Configuration')}</span></dd>
        </dl>
    </section>
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
{/if}
