{*
    Automated SSL promotion: shown on the dashboard instead of the certificate pipeline until an ACME and an AIS
    product are enabled. Styles: theme.css (.ssl-promo). The four messages rotate (CSS animation).
*}
{$w = 'widgets.automated_ssl_banner'}
<div class="tssggs-promo-wrap">
    <span class="tssggs-html">
        <section class="ssl-promo" aria-label="{t key="{$w}.label"}">
            <div class="ssl-promo-halo" aria-hidden="true"></div>
            <div class="ssl-promo-content">
                <div class="ssl-promo-copy">
                    <p class="ssl-promo-eyebrow">{t key="{$w}.eyebrow"}</p>
                    <h3 class="ssl-promo-title">{t key="{$w}.title"}</h3>
                    <div class="ssl-promo-messages" aria-live="off">
                        {foreach [1, 2, 3, 4] as $number}
                            <p class="ssl-promo-msg">{t key="{$w}.message_{$number}"}</p>
                        {/foreach}
                    </div>
                    <div class="ssl-promo-chips">
                        {foreach ['issue', 'renew', 'install'] as $chip}
                            <span class="ssl-promo-chip"><span class="ssl-promo-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg></span>{t key="{$w}.chip_{$chip}"}</span>
                        {/foreach}
                    </div>
                </div>
                <div class="ssl-promo-actions">
                    <div class="ssl-promo-shield" aria-hidden="true"><span class="ssl-promo-ring"></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-8 9-8 9s-8-4-8-9V5l8-3 8 3z"/><path d="m9 12 2 2 4-4"/></svg></div>
                    <div>
                        <a class="ssl-promo-cta" href="{url page='products'}">{t key="{$w}.cta"}<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
                        <p class="ssl-promo-note">{t key="{$w}.note"}</p>
                    </div>
                </div>
            </div>
        </section>
    </span>
</div>
