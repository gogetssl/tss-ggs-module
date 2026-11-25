{*
    Module update page. Plain form posts (no htmx): the last step replaces the module's files.
    Styles: theme.css (.tssggs-upd).
*}
<span class="tssggs-html">
<div class="tssggs-upd">
    {if $error}
        <div class="tssggs-upd__msg is-error">{$error}</div>
    {elseif $notice}
        <div class="tssggs-upd__msg is-ok">{$notice}</div>
    {/if}

    {if $running}
        {* an install in progress: Alpine posts the next step; the button is there if that does not happen *}
        <div class="tssggs-upd__card">
            <h3>{t key='installing' version=$version}</h3>
            <ol class="tssggs-upd__steps">
                {foreach $running.steps as $step}
                    <li class="{$step.state}">{t key="step_{$step.key}"}</li>
                {/foreach}
            </ol>
            <p>{t key='installing_wait'}</p>
            <form method="post" action="{url action='step'}" x-data x-init="setTimeout(() => $el.submit(), 400)">
                <input type="hidden" name="token" value="{$token}">
                <input type="hidden" name="step" value="{$running.next}">
                <button type="submit" class="tssggs-upd__btn is-primary">{t key='continue'}</button>
            </form>
        </div>
    {else}
        <div class="tssggs-upd__card">
            <dl class="tssggs-upd__facts">
                <dt>{t key='installed'}</dt>
                <dd>{$installed}</dd>
                <dt>{t key='newest'}</dt>
                <dd>{$version|default:'-'}{if $page} <a href="{$page}" target="_blank" rel="noopener">{t key='release_page'}</a>{/if}</dd>
                <dt>{t key='checked'}</dt>
                <dd>{$checkedAt}{if $checkError} <span class="tssggs-upd__muted">({$checkError})</span>{/if}</dd>
            </dl>
            <form method="post" action="{url action='check'}" class="tssggs-upd__form">
                <input type="hidden" name="token" value="{$token}">
                <button type="submit" class="tssggs-upd__btn is-plain">{t key='check_now'}</button>
            </form>
        </div>

        {if $available}
            <div class="tssggs-upd__card">
                <h3>{t key='available' version=$version}</h3>
                {if $notes}<div class="tssggs-upd__notes">{$notes}</div>{/if}
                <h4>{t key='checks'}</h4>
                <ul class="tssggs-upd__checks">
                    {foreach $checks as $check}
                        <li class="{if $check.ok}is-ok{elseif $check.blocking}is-fail{else}is-warn{/if}">
                            <b>{t key="check_{$check.key}"}</b>{if $check.detail !== ''} <span class="tssggs-upd__muted">{$check.detail}</span>{/if}
                        </li>
                    {/foreach}
                </ul>
                {if $checksOk && $fullAdmin}
                    <p>{t key='install_info'}</p>
                    <form method="post" action="{url action='step'}" class="tssggs-upd__form"
                          data-confirm="{t key='install_confirm' version=$version}" @submit="confirm($el.dataset.confirm) || $event.preventDefault()" x-data>
                        <input type="hidden" name="token" value="{$token}">
                        <input type="hidden" name="step" value="prepare">
                        <button type="submit" class="tssggs-upd__btn is-primary">{t key='install' version=$version}</button>
                    </form>
                {elseif $checksOk}
                    <p>{t key='full_admin_only'}</p>
                {else}
                    <p class="tssggs-upd__manual">{t key='manual_only'}{if $page} <a href="{$page}" target="_blank" rel="noopener">{t key='download' version=$version}</a>{/if}</p>
                    {if $cliInstall}
                        <p class="tssggs-upd__muted">{t key='cli_hint'} <code>{$cliInstall}</code></p>
                    {/if}
                {/if}
            </div>
        {else}
            <div class="tssggs-upd__card"><p>{t key='up_to_date'}</p></div>
        {/if}

        {if $backup && $fullAdmin}
            <div class="tssggs-upd__card">
                <h4>{t key='rollback_title'}</h4>
                {if !$rollbackOk}
                    <p>{t key='rollback_unsafe'}</p>
                {else}
                    <p>{t key='rollback_info' version=$backup}</p>
                    {if $canSwap}
                        <form method="post" action="{url action='rollback'}" class="tssggs-upd__form"
                              data-confirm="{t key='rollback_confirm' version=$backup}" @submit="confirm($el.dataset.confirm) || $event.preventDefault()" x-data>
                            <input type="hidden" name="token" value="{$token}">
                            <button type="submit" class="tssggs-upd__btn is-plain">{t key='rollback' version=$backup}</button>
                        </form>
                    {else}
                        <p class="tssggs-upd__muted">{t key='rollback_cli' command=$cliRollback}</p>
                    {/if}
                {/if}
            </div>
        {/if}

        {if $history}
            <div class="tssggs-upd__card">
                <h4>{t key='history'}</h4>
                <table class="tssggs-upd__history">
                    <tr><th>{t key='h_date'}</th><th>{t key='h_change'}</th><th>{t key='h_result'}</th></tr>
                    {foreach $history as $entry}
                        <tr>
                            <td>{$entry.at}</td>
                            <td>{$entry.change}</td>
                            <td>{$entry.result}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {/if}

        <p class="tssggs-upd__muted">{t key='recover_hint' path=$recover}</p>
    {/if}
</div>
</span>
