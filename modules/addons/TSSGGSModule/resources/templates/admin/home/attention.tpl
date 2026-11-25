{* Needs your attention ($items): each with the page that fixes it; "nothing" when the list is empty *}
{$w = 'widgets.attention_widget'}
<div class="tssggs-dcard">
    <span class="tssggs-html">
        <div class="tssggs-dcard__head">
            <h3>{if $items}{tile icon='alert-outline' tone='warn'}{else}{tile icon='alert-outline' tone='ok'}{/if}{t key="{$w}.title"}</h3>
            <span class="tssggs-dcard__aside">{$count}</span>
        </div>
    </span>
    <span class="tssggs-html">
        <div class="tssggs-dcard__body">
            <div class="tssggs-alerts">
                {foreach $items as $item}
                    <div class="tssggs-alertrow">
                        {tile icon=$item.icon tone=$item.tone}
                        <div><h4>{$item.title}</h4><p>{$item.text}</p></div>
                        <a class="tssggs-sbtn" href="{$item.url}">{icon name=$item.buttonIcon}{$item.button}</a>
                    </div>
                {foreachelse}
                    <div class="tssggs-alertrow">
                        {tile icon='check' tone='ok'}
                        <div><h4>{t key="{$w}.none"}</h4><p>{t key="{$w}.none_detail"}</p></div>
                    </div>
                {/foreach}
            </div>
        </div>
    </span>
</div>
