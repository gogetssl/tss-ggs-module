{*
    Settings > Email notifications: every e-mail of the module by group, whether it is on, and Enable / Disable.
    The buttons post to the Emails page (action "toggle"), which comes back here. $extra: App\Libs\Settings\EmailScreen.
    Texts: $w (this screen), admin.emails.* (shared with the Emails page). Styles: .em-* (migration.css).
*}
<span class="tssggs-html">
    <div class="tssggs-emails">
        {if $extra.denied !== ''}
            <div class="tssggs-note is-bad">{$extra.denied}</div>
        {else}
            {if $extra.error !== ''}<div class="tssggs-note is-bad">{$extra.error}</div>{/if}
            {if $extra.notice !== ''}<div class="tssggs-note is-ok">{$extra.notice}</div>{/if}
            <table class="em-list">
                <thead>
                    <tr><th>{t key="{$w}.col_email"}</th><th>{t key="{$w}.col_when"}</th><th>{t key="{$w}.col_status"}</th><th></th></tr>
                </thead>
                <tbody>
                    {foreach $extra.groups as $group => $info}
                        {* a group: coloured bar, title, what the e-mails are about, how many *}
                        <tr class="em-gap"><td colspan="4"></td></tr>
                        <tr class="em-group em-group--{$group}">
                            <td colspan="4">
                                <span class="em-group__dot"></span>
                                <b class="em-group__title">{t key="admin.emails.group_{$group}"}</b>
                                <small class="em-group__desc">{t key="admin.emails.group_{$group}_desc"}</small>
                                <em class="em-group__count">{$info.count}</em>
                            </td>
                        </tr>
                        {foreach $info.rows as $row}
                            <tr class="{if $row.disabled}is-off{/if}{if $row@last} is-last{/if}">
                                <td><strong>{t key="admin.emails.name_{$row.slug}"}</strong></td>
                                <td class="tssggs-muted">{t key="admin.emails.when_{$row.slug}"}</td>
                                <td>
                                    {if $row.disabled}<span class="em-tag is-off">{t key='admin.emails.badge_disabled'}</span>{else}<span class="em-tag is-on">{t key="{$w}.enabled"}</span>{/if}
                                    {if $row.unused} <span class="em-tag">{t key='admin.emails.badge_unused'}</span>{/if}
                                    {if $row.customised} <span class="em-tag is-edited">{t key='admin.emails.badge_customised'}</span>{/if}
                                </td>
                                <td class="em-actions">
                                    {if $extra.canEdit}
                                        <form method="post" action="{url page='emails' action='toggle'}" class="tssggs-inline">
                                            <input type="hidden" name="token" value="{$token}">
                                            <input type="hidden" name="key" value="{$row.key}">
                                            <input type="hidden" name="return" value="settings">
                                            {if !$row.disabled}<input type="hidden" name="disable" value="1">{/if}
                                            <button type="submit" class="mod-btn mod-btn--sm {if $row.disabled}mod-btn--success{else}mod-btn--default{/if}">{if $row.disabled}{t key='admin.emails.enable'}{else}{t key='admin.emails.disable'}{/if}</button>
                                        </form>
                                    {/if}
                                    <a class="mod-btn mod-btn--sm mod-btn--default" href="{url page='emails' key=$row.key}">{t key="{$w}.edit"}</a>
                                </td>
                            </tr>
                        {/foreach}
                    {/foreach}
                </tbody>
            </table>
        {/if}
    </div>
</span>
