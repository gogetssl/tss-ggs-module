{* Announcements of the vendor ($items; body is HTML the library has reduced to a few harmless tags) *}
{$w = 'widgets.announcements_widget'}
<div class="tssggs-dcard">
    <span class="tssggs-html">
        <div class="tssggs-dcard__head">
            <h3>{tile icon='bell-outline'}{t key="{$w}.title"}</h3>
            <span class="tssggs-dcard__aside">{t key="{$w}.latest"}</span>
        </div>
    </span>
    <span class="tssggs-html">
        <div class="tssggs-dcard__body">
            {if $error !== ''}
                <p class="tssggs-muted">{$error}</p>
            {elseif !$items}
                <p class="tssggs-muted">{t key="{$w}.none"}</p>
            {else}
                <div class="tssggs-alerts">
                    {foreach $items as $item}
                        <article class="tssggs-alertrow">
                            {tile icon=$item.icon tone=$item.tone}
                            <div>
                                <h4>{$item.title}</h4>
                                <div class="tssggs-alertrow__text">{$item.body nofilter}</div>
                            </div>
                            <time>{$item.date}</time>
                        </article>
                    {/foreach}
                </div>
            {/if}
        </div>
    </span>
</div>
