{*
    Settings > API: the account of the edition's own vendor, then the other vendor the administrator can connect
    (a switch plus its credentials - only its exclusive products are offered then).
    $extra (App\Libs\Settings\ApiScreen): accounts (vendor, own, section, icon, hasLive, hasTest), scope.
    A stored token is never sent back to the page: its field shows a mask, and an empty field keeps the stored token.
    Test Connection (in the Save bar, screen.tpl) puts its result into the box at the end of this template.
*}
<div x-data="{ connect: {if $data.connectOther}true{else}false{/if} }" @change="if ($event.target.name === 'connectOther') { connect = $event.target.value === 'on' }">
    {foreach $extra.accounts as $account}
        {$vendor = $account.vendor}
        {include file='admin/settings/section_start.tpl' name=$account.section icon=$account.icon}
            {if !$account.own}
                {include file='admin/settings/field.tpl' type='yesno' name='connectOther'}
            {/if}
            {include file='admin/settings/field.tpl' type='text' name="{$vendor}LivePartnerCode"}
            {if $account.hasLive}
                {include file='admin/settings/field.tpl' type='password' name="{$vendor}LiveAuthToken" placeholder='************'}
            {else}
                {include file='admin/settings/field.tpl' type='password' name="{$vendor}LiveAuthToken"}
            {/if}
            {if $vendor === 'tss'}
                {include file='admin/settings/field.tpl' type='text' name='tssTestPartnerCode'}
                {if $account.hasTest}
                    {include file='admin/settings/field.tpl' type='password' name='tssTestAuthToken' placeholder='************'}
                {else}
                    {include file='admin/settings/field.tpl' type='password' name='tssTestAuthToken'}
                {/if}
                {include file='admin/settings/field.tpl' type='select' name='OperationMode'}
            {/if}
        {include file='admin/settings/section_end.tpl'}
    {/foreach}

    {* with Connect on, the buttons work on both accounts *}
    <span x-show="connect" x-cloak><div class="tssggs-sec__desc tssggs-api-scope"><p>{$extra.scope}</p></div></span>
    <div id="tssggs-settings-test"></div>
</div>
