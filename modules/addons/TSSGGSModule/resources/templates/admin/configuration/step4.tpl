{*
    Wizard page 4 "Connected to API": the account of the edition's own vendor ($vendor) - the fields of
    Settings > API (admin/settings/field.tpl; $f, $data, $options as on a settings screen). "Next Step" tests the
    connection, saves and goes on; "Test Connection" only tests. What they say goes into the result box.
    A stored token is never sent back to the page: its field shows a mask, and an empty field keeps the stored token.
*}
{if $prefilled !== ''}
    <span class="tssggs-html"><div class="tssggs-note is-ok">{$prefilled}</div></span>
{/if}
<form method="post" action="{$saveUrl}" class="mod-w-full" hx-post="{$saveUrl}" hx-target="#tssggs-settings-test" hx-swap="innerHTML">
    <input type="hidden" name="token" value="{$token}">
    <div id="tssggs-settings-test">
        {if $flash}<div class="tssggs-note is-{$flash.tone}" role="status">{$flash.text nofilter}</div>{/if}
    </div>
    <div class="tssggs-wizard__api">
        {include file='admin/settings/section_start.tpl' name=$vendor icon='key-outline'}
            {include file='admin/settings/field.tpl' type='text' name="{$vendor}LivePartnerCode"}
            {if $hasLive}
                {include file='admin/settings/field.tpl' type='password' name="{$vendor}LiveAuthToken" placeholder='************'}
            {else}
                {include file='admin/settings/field.tpl' type='password' name="{$vendor}LiveAuthToken"}
            {/if}
            {if $vendor === 'tss'}
                {include file='admin/settings/field.tpl' type='text' name='tssTestPartnerCode'}
                {if $hasTest}
                    {include file='admin/settings/field.tpl' type='password' name='tssTestAuthToken' placeholder='************'}
                {else}
                    {include file='admin/settings/field.tpl' type='password' name='tssTestAuthToken'}
                {/if}
                {include file='admin/settings/field.tpl' type='select' name='OperationMode'}
            {/if}
        {include file='admin/settings/section_end.tpl'}
    </div>
    <div class="mod-bar-tools">
        <button type="submit" class="mod-btn mod-btn--success"><span class="mod-btn-text">{t key="{$f}.next_step"}</span></button>
        <button type="submit" class="mod-btn mod-btn--primary" formaction="{$testUrl}" data-test
                hx-post="{$testUrl}" hx-target="#tssggs-settings-test" hx-swap="innerHTML"><span class="mod-btn-text">{t key="{$f}.testConnection"}</span></button>
    </div>
</form>
