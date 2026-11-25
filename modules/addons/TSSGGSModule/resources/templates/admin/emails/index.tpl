{*
    Emails page: the list of e-mails on the left, the editor of the chosen one on the right.
    One form, several buttons: each button posts to its own action (formaction). The editor behaviour (TinyMCE,
    merge-field chips) is in resources/assets/js/pages/emails.js. Styles: .em-* in the module's CSS.
*}
<span class="tssggs-html">
{if $denied}
    <div class="tssggs-note is-bad">{$denied}</div>
{else}
    {if $error}<div class="tssggs-note is-bad">{$error}</div>{/if}
    {if $notice}<div class="tssggs-note is-ok">{$notice}</div>{/if}

    {if $showAbout}
        <div class="tssggs-explain">
            <span class="tssggs-explain__icon">{icon name='information-outline'}</span>
            <div>
                <h3>{t key='about_title'}</h3>
                <p>{t key='intro'}</p>
                <ul>
                    <li>{t key='about_edit'}</li>
                    <li>{t key='about_fields'}</li>
                </ul>
            </div>
        </div>
    {/if}

    <div class="em-layout">
        <div class="em-card em-nav">
            {foreach $groups as $group => $rows}
                <h4>{t key="group_$group"}</h4>
                {foreach $rows as $row}
                    <a class="{if $row.current}is-current{/if}" href="{url key=$row.key}">
                        <span>{t key="name_{$row.slug}"}</span>
                        <span>
                            {if $row.disabled} <span class="em-tag is-off">{t key='badge_disabled'}</span>{/if}
                            {if $row.unused} <span class="em-tag ">{t key='badge_unused'}</span>{/if}
                            {if $row.customised} <span class="em-tag is-edited">{t key='badge_customised'}</span>{/if}
                        </span>
                    </a>
                {/foreach}
            {/foreach}
        </div>

        <div class="em-card">
            <div class="em-head">
                <h3>{t key="name_$slug"}</h3>
                <p>{t key="when_$slug"}</p>
                <p class="tssggs-muted">{t key='whmcs_name'} <code>{$whmcsName}</code></p>
            </div>

            <div class="em-tabs" x-data>
                <a class="{if $language === ''}is-current{/if}" href="{url key=$key}">{t key='language_tab_default'}</a>
                {foreach $tabs.own as $tab}
                    <a class="{if $tab.current}is-current{/if}" href="{url key=$key lang=$tab.lang}">{$tab.name}</a>
                {/foreach}
                {if $tabs.free && $canEdit}
                    <select class="form-control" @change="if ($el.value) { location.href = $el.value }">
                        <option value="">+ {t key='add_language'}</option>
                        {foreach $tabs.free as $tab}
                            <option value="{url key=$key lang=$tab.lang}">{$tab.name}</option>
                        {/foreach}
                    </select>
                {/if}
            </div>

            {if $language !== '' && !$own}
                <div class="tssggs-note is-warn">{t key='language_new' language=$languageName}</div>
            {elseif $language === ''}
                <p class="tssggs-muted em-hint">{t key='language_default_hint'}</p>
            {/if}

            <form method="post" action="{url action='save'}" data-em-form>
                <input type="hidden" name="token" value="{$token}">
                <input type="hidden" name="key" value="{$key}">
                <input type="hidden" name="lang" value="{$language}">

                {* sender, copies, plain text and disabled belong to the default text only (as in WHMCS) *}
                {if $language === ''}
                    <div class="em-opts">
                        <label>{t key='opt_from'}</label>
                        <span class="em-inline">
                            <input class="form-control" name="fromname" placeholder="{t key='opt_from_name'}" value="{$text.fromname}"{if !$canEdit} readonly{/if}>
                            <input class="form-control" name="fromemail" placeholder="{t key='opt_from_email'}" value="{$text.fromemail}"{if !$canEdit} readonly{/if}>
                        </span>
                        <label>{t key='opt_copy'}</label>
                        <input class="form-control" name="copyto" placeholder="{t key='opt_list_hint'}" value="{$text.copyto}"{if !$canEdit} readonly{/if}>
                        <label>{t key='opt_blind'}</label>
                        <input class="form-control" name="blind_copy_to" placeholder="{t key='opt_list_hint'}" value="{$text.blind_copy_to}"{if !$canEdit} readonly{/if}>
                        <label>{t key='opt_plain'}</label>
                        <span><input type="checkbox" name="plaintext" value="1" data-em-plain{if $text.plaintext} checked{/if}{if !$canEdit} disabled{/if}> {t key='opt_plain_hint'}</span>
                        <label>{t key='opt_disable'}</label>
                        <span><input type="checkbox" name="disabled" value="1"{if $isDisabled} checked{/if}{if !$canEdit} disabled{/if}> {t key='opt_disable_hint'}</span>
                    </div>
                {/if}

                <label class="em-label">{t key='subject'}</label>
                <input class="form-control" name="subject" value="{$text.subject}"{if !$canEdit} readonly{/if}>
                <div class="em-labelrow">
                    <label class="em-label">{t key='message'}</label>
                    <label class="em-rich"><input type="checkbox" data-em-rich{if !$text.plaintext} checked{/if}> {t key='rich_editor'}</label>
                </div>
                <textarea class="form-control em-message" id="tssggs-email-message" data-em-message name="message"{if !$canEdit} readonly{/if}>{$text.message}</textarea>
                <p class="tssggs-note is-warn" data-em-rich-failed hidden>{t key='rich_failed'}</p>

                <div class="em-fields">
                    <small>{t key='fields_own'}</small>
                    {foreach $ownFields as $field}
                        <a href="#" class="em-chip is-own" data-em-insert="{ldelim}${$field}{rdelim}">{ldelim}${$field}{rdelim}</a>
                    {/foreach}
                    <small>{t key='fields_standard'}</small>
                    {foreach $moreFields as $field}
                        <a href="#" class="em-chip" data-em-insert="{ldelim}${$field}{rdelim}">{ldelim}${$field}{rdelim}</a>
                    {/foreach}
                </div>

                {if $canEdit}
                    <div class="em-bar">
                        <button class="lu-btn lu-btn--success" type="submit" formaction="{url action='save'}">{t key='save'}</button>
                        <button class="lu-btn lu-btn--default" type="submit" formaction="{url action='preview'}">{t key='preview'}</button>
                        <button class="lu-btn lu-btn--default" type="submit" formaction="{url action='test'}" data-confirm="{t key='confirm_test'}">{t key='test'}</button>
                        <span class="em-spacer"></span>
                        {if $language === ''}
                            <button class="lu-btn lu-btn--default is-danger" type="submit" formaction="{url action='reset'}" data-confirm="{t key='confirm_reset'}">{t key='reset'}</button>
                        {elseif $own}
                            <button class="lu-btn lu-btn--default is-danger" type="submit" formaction="{url action='removeLanguage'}" data-confirm="{t key='confirm_remove_language'}">{t key='remove_language' language=$languageName}</button>
                        {/if}
                    </div>
                {/if}
            </form>

            {if $preview}
                <div class="em-preview">
                    <label class="em-label">{t key='preview_title'}</label>
                    <p><strong>{t key='subject'}:</strong> {$preview.subject}</p>
                    <iframe sandbox="" srcdoc="{$preview.body}"></iframe>
                    <p class="tssggs-muted">{t key='preview_hint'}</p>
                </div>
            {/if}
        </div>
    </div>

    <script src="{$assets}/assets/js/pages/emails.js?v={$version}"></script>
{/if}
</span>
