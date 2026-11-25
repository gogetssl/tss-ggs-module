{*
    Settings > CRON: the live panel (is the cron running, countdown to the next start, a lamp per job), the ONE cron
    line of the module, then a block per job: what it does, its state, how often it runs, when it ran last.
    $extra (App\Libs\Settings\CronScreen): live, line, jobs. Texts: $w (the screen), $f (the jobs).
    Styles: theme.css (.tssggs-cronlive), shell.css (.tssggs-cronone, .tssggs-cronjob).
*}
{if $extra.live}
    <span>
        <div class="tssggs-cronlive is-{$extra.live.state}" style="--n:{$extra.jobs|@count}">
            <div class="tssggs-cronlive__top">
                <span class="tssggs-cronlive__dot"></span>
                <div class="tssggs-cronlive__copy">
                    <div class="tssggs-cronlive__title">{$extra.live.title}</div>
                    <div class="tssggs-cronlive__sub">{$extra.live.sub nofilter}</div>
                </div>
                {if $extra.live.countdown}
                    {* the countdown to the next start runs in the browser *}
                    <div class="tssggs-cronlive__next">
                        {t key="{$w}.liveNextRun"}
                        <b data-due="{t key="{$w}.liveDueNow"}" x-data="{ left: {$extra.live.left} }" x-init="setInterval(function () { if (left > 0) { left-- } }, 1000)"
                           x-text="left > 0 ? String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0') : $el.dataset.due">{t key="{$w}.liveDueNow"}</b>
                    </div>
                {/if}
            </div>
            <div class="tssggs-cronlive__jobs">
                <ul>
                    {foreach $extra.jobs as $job}
                        <li class="is-{$job.state}" style="--i:{$job@index}"><span></span>{t key="{$w}.lamp_{$job.name}"}</li>
                    {/foreach}
                </ul>
                <div class="tssggs-cronlive__sum"><span>{$extra.live.healthy}</span>{if $extra.live.attention !== ''}<b>{$extra.live.attention}</b>{/if}</div>
            </div>
        </div>
    </span>
{/if}

<span>
    <div class="tssggs-cronone" x-data="{ copied: false }">
        <div class="tssggs-cronone__title">{icon name='console'}{$extra.oneLineTitle nofilter}</div>
        <p>{t key="{$w}.oneLineInfo"}</p>
        <div class="tssggs-cronone__code">
            <code x-ref="line">{$extra.line}</code>
            <button type="button" class="tssggs-copy" @click="navigator.clipboard.writeText($refs.line.textContent); copied = true; setTimeout(function () { copied = false }, 1500)">
                {icon name='content-copy'}<span x-show="!copied">{t key="{$w}.copy"}</span><span x-show="copied" x-cloak>{t key="{$w}.copied"}</span>
            </button>
        </div>
    </div>
</span>

{foreach $extra.jobs as $job}
    <div class="lu-widget tssggs-cronjob">
        <div class="lu-widget__body">
            <div class="lu-widget__content">
                <span>
                    <div class="tssggs-cronjob__head">
                        <h3>{t key="{$f}.name_{$job.name}"}</h3>
                        {if $job.state === 'idle'}
                            <span class="tssggs-badge tssggs-badge--idle">{icon name='clock-outline'}{t key="{$f}.neverRunBadge"}</span>
                        {elseif $job.state === 'bad'}
                            <span class="tssggs-badge tssggs-badge--bad">{icon name='alert-outline'}{t key="{$f}.failing"}</span>
                        {else}
                            <span class="tssggs-badge tssggs-badge--ok">{icon name='check-circle-outline'}{t key="{$f}.healthy"}</span>
                        {/if}
                    </div>
                    <p class="tssggs-cronjob__about">{t key="{$f}.about_{$job.name}"}</p>
                    {if $job.state === 'bad'}
                        <div class="tssggs-cronjob__fail">{t key="{$f}.lastRun"} {$job.last} &mdash; {$job.error}</div>
                    {/if}
                    <hr class="tssggs-cronjob__sep">
                </span>
                <span>
                    <div class="tssggs-cronjob__freq">
                        {icon name='clock-outline'}<span>{t key="{$f}.howOften"}</span>
                        {if !$job.key}<span class="tssggs-cronjob__fixed">{t key="{$f}.everyStart"}</span>{/if}
                    </div>
                </span>
                {if $job.key}
                    <div class="lu-form-group">
                        <select name="{$job.key}" class="lu-form-control" required>
                            {foreach $job.choices as $choice => $text}
                                <option value="{$choice}"{if $data[$job.key]|default:'' === $choice} selected{/if}>{$text}</option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
                <span>
                    <p class="tssggs-cronjob__meta">
                        <span>{icon name='clock-outline'}{t key="{$f}.lastRun"} {if $job.last}{$job.last}{else}{t key="{$f}.neverRun"}{/if}</span>
                        <span>{icon name='console'}{t key="{$f}.runNow"} <code>php -q &hellip;/cron/cron.php {$job.name} --force</code></span>
                    </p>
                </span>
            </div>
        </div>
    </div>
{/foreach}
