{* Settings page: the menu of screens on the left ($menu), the chosen screen on the right (screen.tpl) *}
<div class="mod-wrap mod-w-full mod-solo">
    <div class="mod-row">
        <div class="mod-col-2 mod-solo">
            <div class="mod-box">
                <div class="mod-box-head">
                    <div class="mod-box-top mod-bar"><div class="mod-bar-title"> {t key='widgets.sidebar.title'} </div></div>
                </div>
                <div class="mod-box-body">
                    <div class="mod-menubar-items">
                        <ul class="mod-sidenav mod-nav--row mod-left">
                            {foreach $menu as $item}
                                <li class="mod-nav-item{if $item.active} is-active{/if}">
                                    <a class="mod-nav-link" href="{$item.url}">
                                        <span class="mod-nav-mark">{icon name=$item.icon}</span>
                                        <span class="mod-nav-text">{$item.title}</span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="mod-col-10 mod-solo" id="tssggs-settings-screen">
            {include file='admin/settings/screen.tpl'}
        </div>
    </div>
</div>

<script src="{$assets}/assets/js/core/savebar.js?v={$version}"></script>
