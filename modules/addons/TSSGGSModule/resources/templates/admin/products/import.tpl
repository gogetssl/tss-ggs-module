{*
    Import dialog for the ticked products ($ids, comma separated). Left: how the prices are set (as the vendor's
    selling prices, or cost plus a margin); right: the WHMCS product group - an existing one or a new one.
*}
{$f = 'index.data_tables.products.forms.import_form'}
{include file='admin/partials/dialog_start.tpl' title={t key='index.data_tables.products.modals.import_modal.title'} action={url action='import'} target='#tssggs-products' size='xlg'}
    <input type="hidden" name="ids" value="{$ids}">

    <div class="lu-container lu-w-100" x-data="{ percent: {if $percent}true{else}false{/if}, group: '{$group}' }">
        <div class="lu-row">
            <div class="lu-col-md-6">
                <div class="lu-form-group lu-form-check lu-m-b-2x lu-single-element" @change="percent = $event.target.value === 'on'">
                    {include file='admin/partials/yesno.tpl' name='percent' label={t key="{$f}.pricingTypePercent"} on=$percent danger=false}
                </div>
                <div class="lu-form-group" x-show="percent" x-cloak>
                    <label class="lu-form-label"><span>{t key="{$f}.profitMargin"}</span></label>
                    <input type="number" name="profitMargin" class="lu-form-control" min="0" max="1000" step="any" value="{$margin}" :disabled="!percent">
                    <span class="lu-form-feedback lu-form-feedback--icon">{t key="{$f}.profitMargin_description"}</span>
                </div>
            </div>
            <div class="lu-col-md-6">
                <div class="lu-form-group">
                    <label class="lu-form-label"><span>{t key="{$f}.productGroup"}</span></label>
                    <select name="productGroup" class="lu-form-control" required x-model="group">
                        <option value="">{t key='choseProductGroup'}</option>
                        <option value="new">{t key='createNewProductGroup'}</option>
                        {foreach $groups as $id => $name}
                            <option value="{$id}"{if $group == $id} selected{/if}>{$name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="lu-form-group" x-show="group === 'new'" x-cloak>
                    <label class="lu-form-label"><span>{t key="{$f}.productGroupName"}</span></label>
                    <input type="text" name="productGroupName" class="lu-form-control" value="{$groupName}" :disabled="group !== 'new'" :required="group === 'new'">
                </div>
                {if $needsRate}
                    <div class="lu-form-group">
                        <label class="lu-form-label"><span>{t key="{$f}.rate"}</span></label>
                        <input type="text" name="rate" class="lu-form-control" value="{$rate}" required>
                    </div>
                    {include file='admin/partials/alert.tpl' text={t key="{$f}.info" defaultCurrency=$defaultCurrency}}
                {/if}
            </div>
        </div>
    </div>
{include file='admin/partials/dialog_end.tpl'}
