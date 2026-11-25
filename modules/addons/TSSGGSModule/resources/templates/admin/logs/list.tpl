{*
    The Logs page below its explanation: the debug-mode warning, a card per type ($cards: type, count, title, note,
    icon, button, url), the message of the last change and the list. "Show" on a card narrows the list to that type.
*}
{if $debug !== ''}
    {* shown while a service is in debug mode, so nobody forgets it is on *}
    <span class="tssggs-html">
        <div class="tssggs-note is-bad">
            <b>{t key='debugOn'}</b> {$debug}
            <a href="#" hx-post="{url action='stopDebug'}" hx-target="#tssggs-logs" hx-swap="innerHTML">{t key='debugStop'}</a>
        </div>
    </span>
{/if}

<div class="mod-row">
    {foreach $cards as $card}
        <div class="mod-col mod-solo">
            <div class="mod-box">
                <div class="mod-box-body">
                    <div class="mod-box-content">
                        <span class="mod-text-caption">{$card.title}</span>
                        <div class="mod-line mod-line--apart">
                            <div class="mod-text-xl mod-text-cut">
                                {$card.count}
                                {if $card.note !== ''}
                                    <div class="mod-solo"><span class="tssggs-html tssggs-cardnote"><small>{$card.note}</small></span></div>
                                {/if}
                            </div>
                            <div><div class="mod-solo"><i class="tssggs-icobox mod-flex mod-flex-center">{icon name=$card.icon}</i></div></div>
                        </div>
                    </div>
                    <div class="mod-box-foot mod-box-foot--pad">
                        <a class="mod-btn{if $card.button !== ''} mod-btn--{$card.button}{/if} mod-w-full{if $card.type === $type || ($card.type === 'total' && $type === '')} is-active{/if}" href="{$card.url}"
                           hx-get="{$card.url}" hx-target="#tssggs-logs" hx-swap="innerHTML"><span class="mod-btn-text">{t key='show'}</span></a>
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
</div>

{include file='admin/partials/flash.tpl'}
{include file='admin/partials/table.tpl'}
