{* System health ($health): a row per scheduled task with the time of its last run or what is wrong *}
{$w = 'widgets.system_check_widget'}
<div class="tssggs-dcard">
    <span class="tssggs-html">
        <div class="tssggs-dcard__head">
            <h3>{if $health.ok}{tile icon='pulse' tone='ok'}{else}{tile icon='pulse' tone='bad'}{/if}{t key="{$w}.title"}</h3>
            {if $health.latest !== ''}<span class="tssggs-dcard__aside">{icon name='clock-outline'}{$health.latest}</span>{/if}
        </div>
    </span>
    <span class="tssggs-html">
        <div class="tssggs-dcard__rows" id="tssggs-health">
            {foreach $health.tasks as $task}
                <div class="tssggs-row">
                    {icon name=$task.icon}
                    <span class="tssggs-row__name">{$task.name}</span>
                    <span class="tssggs-row__val">{pill tone=$task.tone label=$task.label icon=$task.pillIcon title=$task.title}</span>
                </div>
            {/foreach}
            {if $health.moduleLogOff}
                <div class="tssggs-row">
                    {icon name='text-box-outline'}
                    <span class="tssggs-row__name">{t key="{$w}.moduleLog"}</span>
                    <span class="tssggs-row__val"><a href="{$health.logsUrl}">{pill tone='warn' label=$health.moduleLogLabel icon='alert-outline' title=$health.moduleLogTitle}</a></span>
                </div>
            {/if}
        </div>
    </span>
    <span class="tssggs-html">
        <a class="tssggs-dcard__foot" href="{$health.cronUrl}">{icon name='cog-outline'}<span>{t key="{$w}.review"}</span>{icon name='arrow-right'}</a>
    </span>
</div>
