{* The four figures at the top of the dashboard ($tiles), for the period chosen in Settings > General *}
<span class="tssggs-html">
    <section class="tssggs-dash__kpis" aria-label="{$period}">
        {foreach $tiles as $tile}
            <article class="tssggs-kpi">
                <div class="tssggs-kpi__top"><h3>{$tile.title}</h3>{tile icon=$tile.icon tone=$tile.tone}</div>
                <p class="tssggs-kpi__value">{$tile.value}</p>
                <p class="tssggs-kpi__detail">{icon name=$tile.detailIcon}{$tile.detail}</p>
            </article>
        {/foreach}
    </section>
</span>
