{*
    Wizard page 2 "System Compatibility Check" ($checks: status passed | warning | failed, name, hint, command,
    required, found, statusText). A failed check that blocks the installation ($blocked) disables "Next step".
*}
<span class="tssggs-html"><p class="tssggs-checks__intro">{if $blocked}{t key='step2.intro_failed'}{else}{t key='step2.intro_passed'}{/if}</p></span>
<span class="tssggs-html">
    <div class="tssggs-checks">
        <table>
            <thead>
                <tr><th>{t key='step2.col_check'}</th><th>{t key='step2.col_required'}</th><th>{t key='step2.col_found'}</th><th>{t key='step2.col_status'}</th></tr>
            </thead>
            <tbody>
                {foreach $checks as $check}
                    <tr class="is-{$check.status}">
                        <td>
                            <span class="tssggs-checks__name">{$check.name}</span>
                            {if $check.hint !== ''}<span class="tssggs-checks__hint">{$check.hint}</span>{/if}
                            {if $check.command !== ''}
                                <span class="tssggs-checks__hint">{t key='step2.command_hint'}</span><code class="tssggs-checks__cmd">{$check.command}</code>
                            {/if}
                        </td>
                        <td>{$check.required}</td>
                        <td>{$check.found}</td>
                        <td><span class="tssggs-checks__status">{$check.statusText}</span></td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</span>
{if $blocked}
    <button type="button" class="lu-btn lu-btn--success mt30_custom disabled" disabled><span class="lu-btn__text">{t key='step2.next_step_btn'}</span></button>
{else}
    <a class="lu-btn lu-btn--success mt30_custom" href="{$next}"><span class="lu-btn__text">{t key='step2.next_step_btn'}</span></a>
{/if}
