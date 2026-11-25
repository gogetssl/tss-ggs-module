{*
    Products page: two tabs - the products (banner, explanation, then the list in #tssggs-products) and the changes
    in the vendor's catalogue. Every action on a product puts list.tpl back into #tssggs-products.
*}
<div class="lu-widget" data-component="tabs-container" x-data="{ tab: '{$tab}' }">
    <div class="lu-widget__header">
        <div class="lu-widget__nav">
            <ul class="lu-nav lu-nav--md lu-nav--h lu-nav--tabs lu-nav--arrow">
                <li class="lu-nav__item" :class="tab === 'products' && 'is-active'">
                    <a class="lu-nav__link" href="#" @click.prevent="tab = 'products'"><span class="lu-nav__link-text">{t key='productsTab'}</span></a>
                </li>
                <li class="lu-nav__item" :class="tab === 'changes' && 'is-active'">
                    <a class="lu-nav__link" href="#" @click.prevent="tab = 'changes'"><span class="lu-nav__link-text">{t key='catalogueTab'}{if $recentChanges} ({$recentChanges}){/if}</span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="lu-widget__body">
        <div class="lu-tab-content">
            <div class="lu-p-2x lu-tab-pane" :class="tab === 'products' && 'is-active'">
                {if $connect !== ''}
                    {* the other vendor's exclusive products: shown until that account is connected *}
                    <span class="tssggs-html">
                        <div class="tssggs-explain tssggs-explain--connect">
                            <span class="tssggs-explain__icon">{icon name='package-variant-closed'}</span>
                            <div>
                                <h3>{t key="connect_title_$connect"}</h3>
                                <p>{t key="connect_text_$connect"} <a href="{$apiSettingsUrl}">{t key='connect_link'}</a></p>
                            </div>
                        </div>
                    </span>
                {/if}

                {if $showAbout}
                    <span class="tssggs-html">
                        <div class="tssggs-explain">
                            <span class="tssggs-explain__icon">{icon name='information-outline'}</span>
                            <div>
                                <h3>{t key='about_title'}</h3>
                                <p>{$aboutIntro}</p>
                                <ul>
                                    <li><b>{t key='about_import_name'}</b>: {t key='about_import'}</li>
                                    <li><b>{t key='about_hide_name'}</b>: {t key='about_hide'}</li>
                                    <li><b>{t key='about_retire_name'}</b>: {t key='about_retire'}</li>
                                    <li><b>{t key='about_fix_name'}</b>: {t key=$aboutFix}</li>
                                    <li><b>{t key='about_cancel_name'}</b>: {t key='about_cancel'}</li>
                                </ul>
                            </div>
                        </div>
                    </span>
                {/if}

                <div id="tssggs-products">
                    {include file='admin/products/list.tpl'}
                </div>
            </div>

            <div class="lu-p-2x lu-tab-pane" :class="tab === 'changes' && 'is-active'">
                <span class="tssggs-html">
                    <div class="tssggs-changes">
                        <p class="tssggs-changes__intro">
                            {t key='catalogue_intro' days=$changeDays} {t key='catalogue_mode' mode=$syncMode}
                            <a href="{$productsSettingsUrl}">{t key='catalogue_settings'}</a>
                        </p>
                        {if !$changes}
                            <p class="tssggs-changes__empty">{t key='catalogue_empty'}</p>
                        {else}
                            <div class="tssggs-changes__scroll">
                                <table class="tssggs-changes__table">
                                    <thead>
                                        <tr>
                                            <th>{t key='catalogue_date'}</th><th>{t key='catalogue_type'}</th><th>{t key='catalogue_product'}</th><th>{t key='catalogue_change'}</th>
                                            <th>{t key='catalogue_before'}</th><th>{t key='catalogue_now'}</th><th>{t key='catalogue_action'}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $changes as $change}
                                            <tr>
                                                <td class="tssggs-changes__date">{$change.date}</td>
                                                <td><span class="tssggs-changes__badge tssggs-changes__badge--{$change.type}">{$change.label}</span></td>
                                                <td>
                                                    {if $change.pid}
                                                        <a href="configproducts.php?action=edit&amp;id={$change.pid}">{$change.product}</a> <span class="tssggs-changes__muted">#{$change.pid}</span>
                                                    {else}
                                                        {$change.product}
                                                    {/if}
                                                    <span class="tssggs-changes__muted">{$change.vendor}</span>
                                                </td>
                                                <td>{$change.field}</td>
                                                <td>{$change.before}</td>
                                                <td>{$change.now}</td>
                                                <td>{$change.action}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {/if}
                    </div>
                </span>
            </div>
        </div>
    </div>
</div>
