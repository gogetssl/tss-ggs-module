{*
    Dashboard. This page is quick: the heading, the setup strip, system health and the migration panel are part of
    it. The panels that read all the sales or ask the vendor come in after the page is shown - each "lazy" block
    below is replaced by its template (kpis.tpl, pipeline.tpl, attention.tpl, gateway.tpl, overview.tpl,
    announcements.tpl, support.tpl). Panels sit in rows of a wide and a narrow one (.tssggs-dash__row).
*}
<span class="tssggs-html">
    <div class="tssggs-dash__head">
        <div>
            <div class="tssggs-dash__crumb">{$edition}{icon name='chevron-right'}{t key='home_container.title'}</div>
            <h2>{t key='home_container.title'}</h2>
        </div>
    </div>
</span>

{if $update !== ''}
    <span class="tssggs-html">
        <div class="tssggs-upd-banner">{icon name='package-variant-closed'}<span>{$update}</span><a href="{url page='moduleUpdate'}">{t key='admin.module_update.banner_link'}</a></div>
    </span>
{/if}

{if $steps}
    {* the setup guide, until all three steps are done *}
    <div class="tssggs-setup">
        <span class="tssggs-html">
            <span class="tssggs-setup__lead">{tile icon='format-list-checks' tone='ok'}{t key='widgets.next_steps_widget.lead'}</span>
            {foreach $steps as $step}
                <a class="tssggs-setup__step{if $step.done} is-done{/if}" href="{$step.url}">
                    {if $step.done}{tile icon='check' tone='ok'}{else}{tile icon=$step.icon}{/if}
                    <span><b>{t key="widgets.next_steps_widget.{$step.key}"}</b><small>{t key="widgets.next_steps_widget.{$step.key}_detail"}</small></span>
                    {if !$step.done}{icon name='arrow-right'}{/if}
                </a>
            {/foreach}
        </span>
    </div>
{/if}

{* height: about each panel's own, so the dashboard does not jump down as the panels arrive *}
{include file='admin/partials/lazy.tpl' url={url action='panel' name='kpis'} height=140}

<div class="tssggs-dash__row">
    {if $showPipeline}
        {include file='admin/partials/lazy.tpl' url={url action='panel' name='pipeline'} height=280}
    {else}
        {include file='admin/home/banner.tpl'}
    {/if}
    {include file='admin/home/health.tpl'}
</div>

<div class="tssggs-dash__row">
    {include file='admin/partials/lazy.tpl' url={url action='panel' name='attention'} height=320}
    {include file='admin/partials/lazy.tpl' url={url action='panel' name='gateway'} height=320}
</div>

<div class="tssggs-dash__row{if !$migration} is-single{/if}">
    {include file='admin/partials/lazy.tpl' url={url action='panel' name='overview'} height=220}
    {if $migration}
        {include file='admin/home/migration.tpl'}
    {/if}
</div>

<div class="tssggs-dash__row">
    {include file='admin/partials/lazy.tpl' url={url action='panel' name='announcements'} height=320}
    {include file='admin/partials/lazy.tpl' url={url action='panel' name='support'} height=320}
</div>

<span class="tssggs-html">
    <div class="tssggs-dash__foot">
        <span>{icon name='shield-check-outline'}{$edition}</span>
        {if $version !== ''}<span>{t key='home_container.version' version=$version}</span>{/if}
    </div>
</span>
