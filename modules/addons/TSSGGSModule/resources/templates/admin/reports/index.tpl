{*
    Reports page: one tab per report. A report is loaded when its tab is shown for the first time (action "report"),
    into #tssggs-report-<name>; its filters and its list reload inside that block.
*}
{if $showInfo}
    {include file='admin/partials/alert.tpl' text={t key='reports_container.reportsInfo'}}
{/if}

<div class="mod-box" data-component="tabs-container" x-data="{ tab: '{$tab}' }">
    <div class="mod-box-head">
        <div class="mod-box-nav">
            <ul class="mod-nav mod-nav--md mod-nav--row mod-nav--tabs mod-nav--arrow">
                {foreach $reports as $report}
                    <li class="mod-nav-item" :class="tab === '{$report.name}' && 'is-active'">
                        <a class="mod-nav-link" href="#" @click.prevent="tab = '{$report.name}'"><span class="mod-nav-text">{$report.title}</span></a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
    <div class="mod-box-body">
        <div class="mod-panes">
            {foreach $reports as $report}
                <div class="mod-pad mod-pane" :class="tab === '{$report.name}' && 'is-active'">
                    {include file='admin/partials/lazy.tpl' id="tssggs-report-{$report.name}" url={url action='report' name=$report.name} when='intersect once' swap='innerHTML'}
                </div>
            {/foreach}
        </div>
    </div>
</div>
