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

<div class="lu-row">
    {foreach $cards as $card}
        <div class="lu-col-md lu-single-element">
            <div class="lu-widget">
                <div class="lu-widget__body">
                    <div class="lu-widget__content">
                        <span class="lu-heading">{$card.title}</span>
                        <div class="lu-rail lu-rail--space-between">
                            <div class="lu-type-3 lu-text-truncate">
                                {$card.count}
                                {if $card.note !== ''}
                                    <div class="lu-single-element"><span class="tssggs-html tssggs-cardnote"><small>{$card.note}</small></span></div>
                                {/if}
                            </div>
                            <div><div class="lu-single-element"><i class="tssggs-icobox lu-d-flex lu-justify-content-center">{icon name=$card.icon}</i></div></div>
                        </div>
                    </div>
                    <div class="lu-widget__actions lu-widget__actions--raised">
                        <a class="lu-btn lu-btn--{$card.button} lu-w-100{if $card.type === $type || ($card.type === 'total' && $type === '')} is-active{/if}" href="{$card.url}"
                           hx-get="{$card.url}" hx-target="#tssggs-logs" hx-swap="innerHTML"><span class="lu-btn__text">{t key='show'}</span></a>
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
</div>

{include file='admin/partials/flash.tpl'}
{include file='admin/partials/table.tpl'}
