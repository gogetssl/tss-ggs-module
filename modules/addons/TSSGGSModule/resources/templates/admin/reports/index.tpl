{*
    Reports page: one tab per report. A report is loaded when its tab is shown for the first time (action "report"),
    into #tssggs-report-<name>; its filters and its list reload inside that block.
*}
{if $showInfo}
    {include file='admin/partials/alert.tpl' text={t key='reports_container.reportsInfo'}}
{/if}

<div class="lu-widget" data-component="tabs-container" x-data="{ tab: '{$tab}' }">
    <div class="lu-widget__header">
        <div class="lu-widget__nav">
            <ul class="lu-nav lu-nav--md lu-nav--h lu-nav--tabs lu-nav--arrow">
                {foreach $reports as $report}
                    <li class="lu-nav__item" :class="tab === '{$report.name}' && 'is-active'">
                        <a class="lu-nav__link" href="#" @click.prevent="tab = '{$report.name}'"><span class="lu-nav__link-text">{$report.title}</span></a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
    <div class="lu-widget__body">
        <div class="lu-tab-content">
            {foreach $reports as $report}
                <div class="lu-p-2x lu-tab-pane" :class="tab === '{$report.name}' && 'is-active'">
                    {include file='admin/partials/lazy.tpl' id="tssggs-report-{$report.name}" url={url action='report' name=$report.name} when='intersect once' swap='innerHTML'}
                </div>
            {/foreach}
        </div>
    </div>
</div>
