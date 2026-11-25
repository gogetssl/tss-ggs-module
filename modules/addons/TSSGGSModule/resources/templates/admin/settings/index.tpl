{* Settings page: the menu of screens on the left ($menu), the chosen screen on the right (screen.tpl) *}
<div class="lu-container lu-w-100 lu-single-element">
    <div class="lu-row">
        <div class="lu-col-md-2 lu-single-element">
            <div class="lu-widget">
                <div class="lu-widget__header">
                    <div class="lu-widget__top lu-top"><div class="lu-top__title"> {t key='widgets.sidebar.title'} </div></div>
                </div>
                <div class="lu-widget__body">
                    <div class="lu-navbar__nav">
                        <ul class="slu-nav lu-nav--h lu-is-left">
                            {foreach $menu as $item}
                                <li class="lu-nav__item{if $item.active} is-active{/if}">
                                    <a class="lu-nav__link" href="{$item.url}">
                                        <span class="lu-nav__icon">{icon name=$item.icon}</span>
                                        <span class="lu-nav__link-text">{$item.title}</span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="lu-col-md-10 lu-single-element" id="tssggs-settings-screen">
            {include file='admin/settings/screen.tpl'}
        </div>
    </div>
</div>

<script src="{$assets}/assets/js/core/savebar.js?v={$version}"></script>
