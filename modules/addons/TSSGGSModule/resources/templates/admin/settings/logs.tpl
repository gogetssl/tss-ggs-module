{*
    Settings > Logs: one switch per log, then "Debug one order".
    WHMCS Module Log shows Yes only while WHMCS has its own Module Log on too; Yes switches it on in WHMCS.
*}
{include file='admin/settings/section_start.tpl' name='logs' icon='text-box-outline'}
    {include file='admin/settings/field.tpl' type='yesno' name='certificateLog'}
    {include file='admin/settings/field.tpl' type='yesno' name='internalLog'}
    {include file='admin/settings/field.tpl' type='yesno' name='eventLog'}
    {include file='admin/settings/field.tpl' type='select' name='apiLogMode'}
    {include file='admin/settings/field.tpl' type='yesno' name='moduleLog'}
    {include file='admin/settings/field.tpl' type='yesno' name='whmcsActivityLog'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='debugLogs' icon='bug'}
    {include file='admin/settings/field.tpl' type='text' name='debugServices'}
    {include file='admin/settings/field.tpl' type='select' name='debugHours'}
{include file='admin/settings/section_end.tpl'}
