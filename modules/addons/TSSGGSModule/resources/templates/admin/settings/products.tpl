{*
    Settings > Products: how the WHMCS product list follows the vendor's catalogue. The changes themselves are listed
    on the Products page, tab "Catalogue changes".
*}
{include file='admin/settings/section_start.tpl' name='catalogueSync' icon='sync' points=['catalogueMode_manual', 'catalogueMode_alterations', 'catalogueMode_alterations_removal', 'catalogueMode_all']}
    {include file='admin/settings/field.tpl' type='select' name='catalogueSyncMode'}
    {include file='admin/settings/field.tpl' type='yesno' name='catalogueChangesEmail'}
{include file='admin/settings/section_end.tpl'}
