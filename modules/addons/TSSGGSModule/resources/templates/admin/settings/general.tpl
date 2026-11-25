{* Settings > General: client area, admin area, service lifecycle, LEI codes (GoGetSSL) *}
{include file='admin/settings/section_start.tpl' name='clientArea' icon='account-group-outline'}
    {include file='admin/settings/field.tpl' type='yesno' name='clientSummary'}
    {include file='admin/settings/field.tpl' type='multi' name='sidebarTemplates' placeholder={t key="{$f}.sidebarTemplates_placeholder"}}
    {include file='admin/settings/field.tpl' type='select' name='expiresSoonDays'}
    {include file='admin/settings/field.tpl' type='yesno' name='certificatesByDomain'}
    {include file='admin/settings/field.tpl' type='yesno' name='sslTools'}
    {* where to see these client area pages with examples: the edition's Wiki *}
    <span><div class="tssggs-note">{t key="{$f}.clientAreaWiki"} <a href="{$wiki}" target="_blank" rel="noopener">{t key="{$f}.clientAreaWikiLink"}</a></div></span>
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='interface' icon='view-dashboard-outline'}
    {include file='admin/settings/field.tpl' type='yesno' name='hideInfoMessages'}
    {include file='admin/settings/field.tpl' type='select' name='dashboardPeriod'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='lifecycle' icon='sync'}
    {include file='admin/settings/field.tpl' type='yesno' name='terminateCancelsOrder'}
{include file='admin/settings/section_end.tpl'}

{if in_array('ggs', $vendors)}
    {*
        LEI codes (GoGetSSL): the company search of the order form. The token field shows only while the switch is on.
        A saved token is never sent back to the page: the field shows a mask, and an empty field keeps the saved token.
    *}
    {include file='admin/settings/section_start.tpl' name='lei' icon='getlei'}
        <div x-data="{ on: {if $data.openCorporates}true{else}false{/if} }" @change="if ($event.target.name === 'openCorporates') { on = $event.target.value === 'on' }">
            {include file='admin/settings/field.tpl' type='yesno' name='openCorporates'}
            <div x-show="on" x-cloak>
                {if $hasOpenCorporatesToken}
                    {include file='admin/settings/field.tpl' type='password' name='openCorporatesToken' placeholder='************'}
                {else}
                    {include file='admin/settings/field.tpl' type='password' name='openCorporatesToken'}
                {/if}
            </div>
        </div>
    {include file='admin/settings/section_end.tpl'}
{/if}
