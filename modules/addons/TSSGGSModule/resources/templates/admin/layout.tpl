{*
    Admin layout of the module: header, main menu, page title, then the page's own template in {$body}.
    To change it, copy this file to overrides/templates/admin/layout.tpl and edit the copy.
*}
<div id="mod-root" data-token="{$token}">
    <div class="mod-shell">
        {if $chrome}
        <div>
            <div class="mod-shell-head mod-shell-head--compact mod-menubar tssggs-head">
                <a href="{$homeUrl}" class="tssggs-head__brand">
                    <span class="tssggs-head__logo">{include file='admin/partials/logo.tpl'}</span>
                    <span class="tssggs-head__text"><strong>{$module}</strong><small>SSL certificate management</small></span>
                </a>
            </div>
            <div class="mod-shell-menu mod-menubar mod-menubar--compact mod-slide mod-slide--right">
                <div class="mod-menubar-items">
                    <ul class="mod-nav mod-nav--row mod-left">
                        {foreach $menu as $item}
                            {include file='admin/partials/menu_item.tpl' item=$item}
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
        {/if}

        <div class="mod-main">
            <div class="mod-main-body">
                {if $heading}
                <div class="mod-main-top mod-bar tssggs-pagehead">
                    <div class="tssggs-pagehead__crumb">
                        <span>{$module}</span>
                        {icon name='chevron-right'}
                        <a href="{url}" aria-current="page">{$title}</a>
                    </div>
                    <h2 class="tssggs-pagehead__title">{$title}</h2>
                </div>
                {/if}
                <div></div>
                <div class="mod-main-content mod-solo">
                    <div class="{$bodyClass} mod-solo">
                        {$body nofilter}
                    </div>
                </div>
            </div>
        </div>
        {* where a dialog with a form is put (admin/partials/dialog_start.tpl) *}
        <div id="tssggs-dialog"></div>
    </div>
</div>

{* The dialog that asks before a change (hx-confirm / data-confirm); app.js fills in the title and the question *}
<template data-confirm-template>
    <div class="mod-modal show mod-modal--zoom mod-modal--info">
        <div class="mod-modal-frame">
            <div class="mod-modal-sheet">
                <div class="mod-modal-head mod-bar">
                    <span class="mod-bar-icon mod-text-warning">{icon name='information-outline'}</span>
                    <div class="mod-bar-title mod-text-warning mod-text-lg"><span data-confirm-title></span></div>
                    <div class="mod-bar-tools">
                        <button type="button" class="mod-btn mod-btn--xs mod-btn--default mod-btn--icon mod-btn--link mod-btn--plain mod-modal-close" aria-label="{t key='admin.kernel.cancel'}" data-confirm-no>
                            <span class="mod-btn-icon tssggs-icobox">{icon name='close'}</span>
                        </button>
                    </div>
                </div>
                <div class="mod-modal-body"><p data-confirm-text></p></div>
                <div class="mod-modal-foot">
                    <button type="button" class="mod-btn mod-btn--warning" data-confirm-yes><span class="mod-btn-text">{t key='admin.kernel.confirm'}</span></button>
                    <button type="button" class="mod-btn mod-btn--danger mod-btn--outline mod-btn--plain" data-confirm-no><span class="mod-btn-text">{t key='admin.kernel.cancel'}</span></button>
                </div>
            </div>
        </div>
    </div>
</template>

<div class="clear"></div>

<link rel="stylesheet" href="{$assets}/build/css.php?v={$version}&amp;type=module&amp;isAdminArea=1">
<script src="{$assets}/assets/js/vendor/htmx.js?v={$version}"></script>
<script src="{$assets}/assets/js/app.js?v={$version}"></script>
<script src="{$assets}/assets/js/dropdown.js?v={$version}"></script>
<script defer src="{$assets}/assets/js/vendor/alpine.js?v={$version}"></script>
