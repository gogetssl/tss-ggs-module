{*
    Admin layout of the module: header, main menu, page title, then the page's own template in {$body}.
    To change it, copy this file to overrides/templates/admin/layout.tpl and edit the copy.
*}
<div id="layers2" data-token="{$token}">
    <div class="lu-app">
        <div>
            <div class="lu-app-header lu-app-header--responsive lu-navbar tssggs-head">
                <a href="{$homeUrl}" class="tssggs-head__brand">
                    <span class="tssggs-head__logo">{include file='admin/partials/logo.tpl'}</span>
                    <span class="tssggs-head__text"><strong>{$module}</strong><small>SSL certificate management</small></span>
                </a>
            </div>
            <div class="lu-app-navbar lu-navbar lu-navbar--responsive lu-off-canvas-responsive lu-off-canvas-responsive--right">
                <div class="lu-navbar__nav">
                    <ul class="lu-nav lu-nav--h lu-is-left">
                        {foreach $menu as $item}
                            {include file='admin/partials/menu_item.tpl' item=$item}
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>

        <div class="lu-app-main">
            <div class="lu-app-main__body">
                <div class="lu-app-main__top lu-top tssggs-pagehead">
                    <div class="tssggs-pagehead__crumb">
                        <span>{$module}</span>
                        {icon name='chevron-right'}
                        <a href="{url}" aria-current="page">{$title}</a>
                    </div>
                    <h2 class="tssggs-pagehead__title">{$title}</h2>
                </div>
                <div></div>
                <div class="modulesgarden-app-main-container lu-single-element">
                    <div class="{$bodyClass} lu-single-element">
                        {$body nofilter}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="clear"></div>

<link rel="stylesheet" href="{$assets}/build/css.php?v={$version}&amp;type=module&amp;isAdminArea=1">
<script src="{$assets}/assets/js/vendor/htmx.js?v={$version}"></script>
<script src="{$assets}/assets/js/app.js?v={$version}"></script>
<script defer src="{$assets}/assets/js/vendor/alpine.js?v={$version}"></script>
