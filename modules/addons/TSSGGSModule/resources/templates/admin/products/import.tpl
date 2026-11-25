{*
    Import dialog for the ticked products ($ids, comma separated). Left: how the prices are set (as the vendor's
    selling prices, or cost plus a margin); right: the WHMCS product group - an existing one or a new one.
*}
{$f = 'index.data_tables.products.forms.import_form'}
{include file='admin/partials/dialog_start.tpl' title={t key='index.data_tables.products.modals.import_modal.title'} action={url action='import'} target='#tssggs-products' size='xlg'}
    <input type="hidden" name="ids" value="{$ids}">

    <div class="mod-wrap mod-w-full" x-data="{ percent: {if $percent}true{else}false{/if}, group: '{$group}' }">
        <div class="mod-row">
            <div class="mod-col-6">
                <div class="mod-field mod-check mod-gap-b mod-solo" @change="percent = $event.target.value === 'on'">
                    {include file='admin/partials/yesno.tpl' name='percent' label={t key="{$f}.pricingTypePercent"} on=$percent danger=false}
                </div>
                <div class="mod-field" x-show="percent" x-cloak>
                    <label class="mod-field-label"><span>{t key="{$f}.profitMargin"}</span></label>
                    <input type="number" name="profitMargin" class="mod-input" min="0" max="1000" step="any" value="{$margin}" :disabled="!percent">
                    <span class="mod-field-note mod-field-note--icon">{t key="{$f}.profitMargin_description"}</span>
                </div>
            </div>
            <div class="mod-col-6">
                <div class="mod-field">
                    <label class="mod-field-label"><span>{t key="{$f}.productGroup"}</span></label>
                    <select name="productGroup" class="mod-input" required x-model="group">
                        <option value="">{t key='choseProductGroup'}</option>
                        <option value="new">{t key='createNewProductGroup'}</option>
                        {foreach $groups as $id => $name}
                            <option value="{$id}"{if $group == $id} selected{/if}>{$name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="mod-field" x-show="group === 'new'" x-cloak>
                    <label class="mod-field-label"><span>{t key="{$f}.productGroupName"}</span></label>
                    <input type="text" name="productGroupName" class="mod-input" value="{$groupName}" :disabled="group !== 'new'" :required="group === 'new'">
                </div>
                {if $needsRate}
                    <div class="mod-field">
                        <label class="mod-field-label"><span>{t key="{$f}.rate"}</span></label>
                        <input type="text" name="rate" class="mod-input" value="{$rate}" required>
                    </div>
                    {include file='admin/partials/alert.tpl' text={t key="{$f}.info" defaultCurrency=$defaultCurrency}}
                {/if}
            </div>
        </div>
    </div>
{include file='admin/partials/dialog_end.tpl'}
