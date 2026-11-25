{*
    "Product Configuration" dialog of an imported product ($product from App\Libs\Products\ProductEditor::load).
    The SAN fields are there only for products that have the SAN options.
*}
{$f = 'index.data_tables.products.forms.configuration_form'}
{$p = 'index.data_tables.products.providers.configuration_provider'}
{include file='admin/partials/dialog_start.tpl' title={t key='index.data_tables.products.modals.configuration_modal.title'} action={url action='save'} target='#tssggs-products'}
    <input type="hidden" name="id" value="{$product.id}">

    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.remoteProductName"}</span></label>
        <input type="text" class="lu-form-control" value="{$product.remoteProductName}" readonly disabled>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.productName"}</span></label>
        <input type="text" name="productName" class="lu-form-control" value="{$product.productName}" required>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.description"}</span></label>
        <textarea name="description" class="lu-form-control" rows="4">{$product.description}</textarea>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.autoSetup"}</span></label>
        <select name="autoSetup" class="lu-form-control">
            {foreach ['order' => 'autoSetupOrder', 'payment' => 'autoSetupPayment', 'on' => 'autoSetupOn', 'off' => 'autoSetupOff'] as $value => $text}
                <option value="{$value}"{if $product.autoSetup === $value} selected{/if}>{t key="{$p}.{$text}"}</option>
            {/foreach}
        </select>
    </div>

    {if $product.hasSan}
        <div class="lu-form-group lu-form-check lu-m-b-2x lu-single-element">
            {include file='admin/partials/yesno.tpl' name='enableSan' label={t key="{$f}.enableSan"} on=$product.enableSan}
        </div>
        <div class="lu-form-group">
            <label class="lu-form-label"><span>{t key="{$f}.includedSan"}</span></label>
            <input type="number" name="includedSan" class="lu-form-control" min="0" max="{$product.maxIncluded}" value="{$product.includedSan}">
        </div>
    {/if}
    {if $product.hasWildcard}
        <div class="lu-form-group lu-form-check lu-m-b-2x lu-single-element">
            {include file='admin/partials/yesno.tpl' name='enableWildcard' label={t key="{$f}.enableWildcard"} on=$product.enableWildcard}
        </div>
        <div class="lu-form-group">
            <label class="lu-form-label"><span>{t key="{$f}.includedWildcard"}</span></label>
            <input type="number" name="includedWildcard" class="lu-form-control" min="0" max="{$product.maxIncluded}" value="{$product.includedWildcard}">
        </div>
    {/if}

    <div class="lu-form-group lu-form-check lu-m-b-2x lu-single-element">
        {include file='admin/partials/yesno.tpl' name='hidden' label={t key="{$f}.hidden"} on=$product.hidden}
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.payType"}</span></label>
        <select name="payType" class="lu-form-control">
            {foreach ['recurring' => 'payTypeRecurring', 'onetime' => 'payTypeOnetime', 'free' => 'payTypeFree'] as $value => $text}
                <option value="{$value}"{if $product.payType === $value} selected{/if}>{t key="{$p}.{$text}"}</option>
            {/foreach}
        </select>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.months"}</span></label>
        {* WHMCS bills up to 36 months (Triennially): a longer term only fits a one-time payment *}
        <select name="months" class="lu-form-control">
            <option value="0"{if !$product.months} selected{/if}>{t key="{$p}.monthsBillingCycle"}</option>
            {foreach $product.terms as $months}
                <option value="{$months}"{if $product.months == $months} selected{/if}>{if $months > $maxRecurring}{t key="{$p}.monthsValueOneTime" months=$months}{else}{t key="{$p}.monthsValue" months=$months}{/if}</option>
            {/foreach}
        </select>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.margin"}</span></label>
        <input type="text" name="margin" class="lu-form-control" value="{$product.margin}" placeholder="{t key="{$f}.margin_placeholder"}">
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.processingGuide"}</span></label>
        <textarea name="processingGuide" class="lu-form-control" rows="4">{$product.processingGuide}</textarea>
        <span class="lu-form-feedback lu-form-feedback--icon">{t key="{$f}.processingGuide_description"}</span>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label"><span>{t key="{$f}.issuedMessage"}</span></label>
        <textarea name="issuedMessage" class="lu-form-control" rows="4">{$product.issuedMessage}</textarea>
        <span class="lu-form-feedback lu-form-feedback--icon">{t key="{$f}.issuedMessage_description"}</span>
    </div>
{include file='admin/partials/dialog_end.tpl'}
