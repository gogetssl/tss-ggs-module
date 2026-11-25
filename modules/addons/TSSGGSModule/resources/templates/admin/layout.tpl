{*
    Admin layout of the module: header, main menu, page title, then the page's own template in {$body}.
    To change it, copy this file to overrides/templates/admin/layout.tpl and edit the copy.
*}
<div id="layers2" data-token="{$token}">
    <div class="lu-app">
        {if $chrome}
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
        {/if}

        <div class="lu-app-main">
            <div class="lu-app-main__body">
                {if $heading}
                <div class="lu-app-main__top lu-top tssggs-pagehead">
                    <div class="tssggs-pagehead__crumb">
                        <span>{$module}</span>
                        {icon name='chevron-right'}
                        <a href="{url}" aria-current="page">{$title}</a>
                    </div>
                    <h2 class="tssggs-pagehead__title">{$title}</h2>
                </div>
                {/if}
                <div></div>
                <div class="modulesgarden-app-main-container lu-single-element">
                    <div class="{$bodyClass} lu-single-element">
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
    <div class="lu-modal show modal--zoomIn lu-modal--info">
        <div class="lu-modal__dialog">
            <div class="lu-modal__content">
                <div class="lu-modal__top lu-top">
                    <span class="lu-top__icon lu-text-warning">{icon name='information-outline'}</span>
                    <div class="lu-top__title lu-text-warning lu-type-4"><span data-confirm-title></span></div>
                    <div class="lu-top__toolbar">
                        <button type="button" class="lu-btn lu-btn--xs lu-btn--default lu-btn--icon lu-btn--link lu-btn--plain closeModal" aria-label="{t key='admin.kernel.cancel'}" data-confirm-no>
                            <span class="lu-btn__icon tssggs-icobox">{icon name='close'}</span>
                        </button>
                    </div>
                </div>
                <div class="lu-modal__body"><p data-confirm-text></p></div>
                <div class="lu-modal__actions">
                    <button type="button" class="lu-btn lu-btn--warning" data-confirm-yes><span class="lu-btn__text">{t key='admin.kernel.confirm'}</span></button>
                    <button type="button" class="lu-btn lu-btn--danger lu-btn--outline lu-btn--plain" data-confirm-no><span class="lu-btn__text">{t key='admin.kernel.cancel'}</span></button>
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
