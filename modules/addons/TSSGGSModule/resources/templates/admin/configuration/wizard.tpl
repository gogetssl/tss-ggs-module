{*
    The frame of the setup wizard: the page title, the module header with the progress, the list of steps ($steps:
    state done | active | todo, mark, title, desc, url of a finished step) and the card of the current page
    (admin/configuration/step<N>.tpl, $page). Styles: resources/assets/css/wizard.css, scoped to .tssggs-wizard.
    Texts of a page: "step<N>.<key>" (group admin.configuration.content); of the frame: instalation.wizard.*
*}
<div class="lu-app-main__top lu-top tssggs-pagehead">
    <div class="tssggs-pagehead__crumb">
        <span>{$module}</span>
        {icon name='chevron-right'}
        <span>{t key='admin.breadcrumbs.Configuration'}</span>
        {icon name='chevron-right'}
        <a href="{url action="step{$page}"}" aria-current="page">{$crumb}</a>
    </div>
    <h2 class="tssggs-pagehead__title">{$crumb}</h2>
</div>

<div class="tssggs-wizard">
    <span class="tssggs-html">
        <div class="tssggs-wizard__header">
            <div class="tssggs-wizard__brand">
                <span class="tssggs-wizard__logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
                <span>
                    <span class="tssggs-wizard__name">{$module}</span>
                    <span class="tssggs-wizard__sub">{t key='instalation.wizard.module_installation'}{if $moduleVersion !== ''} &middot; v{$moduleVersion}{/if}</span>
                </span>
            </div>
            <div class="tssggs-wizard__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{$percent}">
                <span class="tssggs-wizard__bar"><span style="width:{$percent}%"></span></span>
                <span class="tssggs-wizard__pct">{$percent}%</span>
            </div>
        </div>
    </span>

    <div class="tssggs-wizard__body">
        <span class="tssggs-html">
            <nav class="tssggs-wizard__nav" aria-label="{t key='instalation.wizard.installation_steps'}">
                <div class="tssggs-wizard__nav-title">{t key='instalation.wizard.installation_steps'}</div>
                <ol>
                    {foreach $steps as $step}
                        <li class="is-{$step.state}">
                            {capture name='inner'}
                                <span class="tssggs-wizard__dot">
                                    {if $step.state === 'active'}<span class="tssggs-wizard__ring"></span>{/if}
                                    {if $step.state === 'done'}
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                    {else}{$step.mark}{/if}
                                </span>
                                <span class="tssggs-wizard__label">
                                    <span class="tssggs-wizard__step-title">{$step.title}</span>
                                    <span class="tssggs-wizard__step-desc">{$step.desc}</span>
                                </span>
                            {/capture}
                            {if $step.url !== ''}
                                <a class="tssggs-wizard__item" href="{$step.url}">{$smarty.capture.inner nofilter}</a>
                            {else}
                                <span class="tssggs-wizard__item"{if $step.state === 'active'} aria-current="step"{/if}>{$smarty.capture.inner nofilter}</span>
                            {/if}
                        </li>
                    {/foreach}
                </ol>
            </nav>
        </span>

        <div class="tssggs-wizard__section">
            <div class="lu-widget tssggs-step">
                <div class="lu-widget__body">
                    <div class="lu-widget__content">
                        <span class="tssggs-html">
                            <div class="tssggs-step__eyebrow">{$stepOf}</div>
                            <h2 class="tssggs-step__title">{$stepTitle}</h2>
                        </span>
                        {include file="admin/configuration/step{$page}.tpl"}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
