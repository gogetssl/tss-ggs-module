{* One entry of the main menu; an entry with $item.items is a dropdown (opened and closed by Alpine) *}
{if $item.items}
    <li class="lu-nav__item lu-has-dropdown{if $item.active} is-active{/if}" x-data="{ open: false }" :class="open && 'tssggs-open'" @click.outside="open = false">
        <a href="{$item.url}" class="lu-nav__link" @click.prevent="open = !open" :aria-expanded="open">
            <span class="lu-nav__link-icon">{icon name=$item.icon}</span>
            <span class="lu-nav__link-text">{$item.title}</span>
            {icon name='chevron-down' class='tssggs-nav-chevron'}
            <span class="lu-nav__link-drop-arrow"></span>
            <span class="drop-arrow"></span>
        </a>
        <ul class="lu-nav lu-nav--sub{if $item.items|@count == 1} lu-single-element{/if}">
            {foreach $item.items as $sub}
                {include file='admin/partials/menu_item.tpl' item=$sub}
            {/foreach}
        </ul>
    </li>
{else}
    <li class="lu-nav__item{if $item.active} is-active{/if}">
        <a href="{$item.url}" class="lu-nav__link">
            <span class="lu-nav__link-icon">{icon name=$item.icon}</span>
            <span class="lu-nav__link-text">{$item.title}</span>
            <span class="lu-nav__link-drop-arrow"></span>
            <span class="drop-arrow"></span>
        </a>
    </li>
{/if}
