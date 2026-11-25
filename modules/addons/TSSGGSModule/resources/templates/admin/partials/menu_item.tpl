{* One entry of the main menu; an entry with $item.items is a dropdown (opened and closed by Alpine) *}
{if $item.items}
    <li class="mod-nav-item mod-has-menu{if $item.active} is-active{/if}" x-data="{ open: false }" :class="open && 'tssggs-open'" @click.outside="open = false">
        <a href="{$item.url}" class="mod-nav-link" @click.prevent="open = !open" :aria-expanded="open">
            <span class="mod-nav-icon">{icon name=$item.icon}</span>
            <span class="mod-nav-text">{$item.title}</span>
            {icon name='chevron-down' class='tssggs-nav-chevron'}
            <span class="mod-nav-caret"></span>
            <span class="mod-pop-arrow"></span>
        </a>
        <ul class="mod-nav mod-nav--sub{if $item.items|@count == 1} mod-solo{/if}">
            {foreach $item.items as $sub}
                {include file='admin/partials/menu_item.tpl' item=$sub}
            {/foreach}
        </ul>
    </li>
{else}
    <li class="mod-nav-item{if $item.active} is-active{/if}">
        <a href="{$item.url}" class="mod-nav-link">
            <span class="mod-nav-icon">{icon name=$item.icon}</span>
            <span class="mod-nav-text">{$item.title}</span>
            <span class="mod-nav-caret"></span>
            <span class="mod-pop-arrow"></span>
        </a>
    </li>
{/if}
