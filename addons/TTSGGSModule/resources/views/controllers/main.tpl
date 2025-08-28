<div id="layers2">
    <div class="lu-app" id="{$vueInstanceName}">
        <div class="lu-app-header lu-app-header--responsive lu-navbar">
            <a class="lu-navbar__brand lu-brand lu-brand--product" href="{$mainURL}">
                <div class="lu-brand__logo lu-product-{$tagImageModule}-for-whmcs lu-i-c-5x">
                    <img class="lu-i-c-3x" src="{$assetsURL}/img/products/{$tagImageModule}.svg" alt="{$mainName}">
                </div>
                <div class="lu-brand__text lu-text-hide">
                    {$mainName}
                </div>
            </a>
            <button class="lu-navbar__burger lu-navbar-right lu-btn" data-toggle="offCanvas" data-target=".lu-app-navbar">
                <span class="lu-btn__icon lu-burger">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
        <div class="lu-app-navbar lu-navbar lu-navbar--responsive lu-off-canvas-responsive lu-off-canvas-responsive--right">
            <div class="lu-navbar__top">
                <a class="lu-navbar__brand lu-brand lu-brand--product" href="{$mainURL}">
                    <div class="lu-brand__logo lu-product-{$tagImageModule}-for-whmcs lu-i-c-6x">
                        <img class="lu-i-c-4x" src="{$assetsURL}/img/products/{$tagImageModule}.svg" alt="{$mainName}">
                    </div>
                    <div class="lu-brand__text lu-text-hide">
                        {$mainName}
                    </div>
                </a>
                <a class="lu-navbar__brand lu-brand lu-is-right" href="https://www.modulesgarden.com" target="_blank">
                    <div class="lu-brand__logo">
                        <img src="{$assetsURL}/img/modules-garden-logo.svg" alt="ModulesGarden" width="150">
                    </div>
                </a>
            </div>
            <div class="lu-navbar__nav">
                <ul class="lu-nav lu-nav--h lu-is-left">
                    {foreach from=$menu key=catName item=category}
                        {if $category.submenu}
                            <li class="lu-nav__item has-dropdown {if $currentPageName|strtolower === $catName|strtolower}is-active{/if}">
                                <a class="lu-nav__link" href="{$category.url}">
                                    {if $category.icon}
                                        <i class="{$category.icon}"></i>
                                    {/if}
                                    {if $category.label}
                                        {$subpage.label}
                                        <span class="lu-nav__link-drop-arrow"></span>
                                    {else}
                                        <span class="lu-nav__link-text">{$MGLANG->T('pagesLabels','label', $catName)}</span>
                                        <span class="lu-nav__link-drop-arrow"></span>
                                    {/if}
                                    <span class="drop-arrow"></span>
                                </a>
                                <ul class="lu-nav lu-nav--sub">
                                    {foreach from=$category.submenu key=subCatName item=subCategory}
                                        {if $subCategory.externalUrl}
                                            <li class="lu-nav__item">
                                                <a class="lu-nav__link" href="{$subCategory.externalUrl}" target="_blank">
                                                    {if $subCategory.icon}<i class="{$subCategory.icon}"></i>{/if}
                                                    {if $subCategory.label}
                                                        {$subCategory.label}
                                                    {else}
                                                        <span class="lu-nav__link-text">{$MGLANG->T('pagesLabels', $catName, $subCatName)}</span>
                                                    {/if}
                                                </a>
                                            </li>
                                        {else}
                                            <li class="lu-nav__item">
                                                <a class="lu-nav__link" href="{$subCategory.url}">
                                                    {if $subCategory.icon}<i class="{$subCategory.icon}"></i>{/if}
                                                    {if $subCategory.label}
                                                        {$subCategory.label}
                                                    {else}
                                                        <span class="lu-nav__link-text">{$MGLANG->T('pagesLabels', $catName, $subCatName)}</span>
                                                    {/if}
                                                </a>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </li>
                        {else}
                            <li class="lu-nav__item {if $currentPageName|strtolower === $catName|strtolower}is-active{/if}">
                                <a class="lu-nav__link" href="{if $category.externalUrl}{$category.externalUrl}{else}{$category.url}{/if}"
                                        {if $category.externalUrl} target="_blank"{/if}>
                                    {if $category.icon}
                                        <i class="{$category.icon}"></i>
                                    {/if}
                                    {if $category.label}
                                        {$subpage.label}
                                    {else}
                                        <span class="lu-nav__link-text">{$MGLANG->T('pagesLabels','label', $catName)}</span>
                                    {/if}
                                    <span class="drop-arrow"></span>
                                </a>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>
        </div>


        <div class="lu-app-main">
            <div class="lu-app-main__body">


                <div class="lu-app-main__top lu-top">
                    <ul class="lu-breadcrumb lu-type-5">
                        {assign var="brKeys"  value=$breadcrumbs|array_keys}
                        {assign var="brLastKey"  value=$brKeys|end}
                        {foreach from=$breadcrumbs key=brKey item=brItem}
                            {if $brItem->getTitle()|strtolower !== 'index'}
                                <li class="lu-breadcrumb__item is-active">
                                    {if $brItem->getUrl() && $brLastKey !== $brKey && !$brItem->isDisabled()}
                                        <a class="lu-breadcrumb__link" href="{$brItem->getUrl()}">
                                            {if $brItem->getRawTitle()}
                                                {$brItem->getRawTitle()}
                                            {elseif $brKeys[0] === $brKey}
                                                {$MGLANG->T('pagesLabels','label', $brItem->getTitle())}
                                            {else}
                                                {$MGLANG->T('pagesLabels', $breadcrumbs[($brKey - 1)]->getTitle(), $brItem->getTitle())}
                                            {/if}
                                        </a>
                                    {else}
                                        <span class="lu-breadcrumb__link">
                                            {if $brItem->getRawTitle()}
                                                {$brItem->getRawTitle()}
                                            {elseif $brKeys[0] === $brKey}
                                                {$MGLANG->T('pagesLabels','label', $brItem->getTitle())}
                                            {else}
                                                {$MGLANG->T('pagesLabels', $breadcrumbs[($brKey - 1)]->getTitle(), $brItem->getTitle())}
                                            {/if}
                                        </span>
                                    {/if}
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>





                {if $error}
                    <div class="lu-alert lu-alert--outline lu-alert--icon lu-alert--danger lu-alert--bordered lu-m-b-x lu-alert--dismiss mg-message">
                        <div class="lu-alert__body">
                            <b>{$error}</b>
                        </div>
                        <button type="button" class="lu-btn lu-btn--icon lu-btn--link lu-btn--close" data-dismiss="alert">
                            <i class="lu-btn__icon lu-mdi mdi mdi-close"></i>
                        </button>
                    </div>
                {/if}


                {if $success}
                    <div class="lu-alert lu-alert--outline lu-alert--icon lu-alert--success lu-alert--bordered lu-m-b-x lu-alert--dismiss mg-message">
                        <div class="lu-alert__body">
                            <b>{$success}</b>
                        </div>
                        <button type="button" class="lu-btn lu-btn--icon lu-btn--link lu-btn--close" data-dismiss="alert">
                            <i class="lu-btn__icon lu-mdi mdi mdi-close"></i>
                        </button>
                    </div>
                {/if}



                {if ($isDebug eq true AND (count($MGLANG->getMissingLangs()) != 0))}
                    <div class="lu-row">
                        <div class="lu-col-md-12">
                            <div class="lu-widget">
                                <div class="lu-widget__body">
                                    <div class="lu-widget__content">
                                        <div class="lu-row">
                                            {foreach from=$MGLANG->getMissingLangs() key=varible item=value}
                                                <div class="lu-col-md-12"><b>{$varible}</b> = '{$value}';</div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}


                {$content}
            </div>
        </div>
    </div>
</div>


<div class="clear"></div>

{include file='partials/css_assets.tpl'}
{include file='partials/js_assets.tpl'}
