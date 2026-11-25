{*
    Settings > SSL Certificates: validation, CSR generator, ordering, downloads, client messages, renewals, and the
    renewal invoices of recurring and one-time orders.
    $extra (App\Libs\Settings\SslScreen): authorities, downloadFormats, technicalFields, families, languages,
    checkCertificate, wiki.
    A block that belongs to a switch is shown only while the switch is on (Alpine: x-data / x-show).
*}
{include file='admin/settings/section_start.tpl' name='sslValidation' icon='format-list-checks'}
    {include file='admin/settings/field.tpl' type='yesno' name='disableEmailValidation'}
    {include file='admin/settings/field.tpl' type='yesno' name='autoDetailsForDvOrders'}
    <div x-data="{ on: {if $data.useFixedTechnicalDetails|default:'0' == '1'}true{else}false{/if} }" @change="if ($event.target.name === 'useFixedTechnicalDetails') { on = $event.target.value === '1' }">
        {include file='admin/settings/field.tpl' type='yesno' name='useFixedTechnicalDetails'}
        {* the technical contact sent with every order *}
        <div x-show="on" x-cloak>
            {foreach $extra.technicalFields as $fieldName}
                {include file='admin/settings/field.tpl' type='text' name=$fieldName}
            {/foreach}
            {include file='admin/settings/field.tpl' type='select' name='country'}
        </div>
    </div>
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='csrGenerator' icon='key-variant'}
    <div x-data="{ on: {if $data.enableCsrGenerator|default:'0' == '1'}true{else}false{/if}, profile: {if $data.useProfileDetailsForCsr|default:'0' == '1'}true{else}false{/if} }"
         @change="if ($event.target.name === 'enableCsrGenerator') { on = $event.target.value === '1' } if ($event.target.name === 'useProfileDetailsForCsr') { profile = $event.target.value === '1' }">
        {include file='admin/settings/field.tpl' type='yesno' name='enableCsrGenerator'}
        <div x-show="on" x-cloak>
            {include file='admin/settings/field.tpl' type='yesno' name='useProfileDetailsForCsr'}
            {* without the client's profile details: the country the CSR generator starts with *}
            <div x-show="!profile" x-cloak>
                {include file='admin/settings/field.tpl' type='select' name='defaultCsrCountry'}
            </div>
        </div>
    </div>
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='ordering' icon='web'}
    {include file='admin/settings/field.tpl' type='yesno' name='useDefaultWebServer'}
    {include file='admin/settings/field.tpl' type='yesno' name='requireAgreement'}
    {* one agreement per certificate authority, picked automatically from the product's brand *}
    {foreach $extra.authorities as $authority}
        {include file='admin/settings/section_start.tpl' name="agreement_{$authority}" sub=true}
            {include file='admin/settings/field.tpl' type='text' name="agreementUrl_{$authority}"}
            {include file='admin/settings/field.tpl' type='textarea' name="agreementText_{$authority}"}
        {include file='admin/settings/section_end.tpl'}
    {/foreach}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='delivery' icon='file-document-outline'}
    {foreach $extra.downloadFormats as $format}
        {include file='admin/settings/field.tpl' type='yesno' name="downloadFormat_{$format}"}
    {/foreach}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='clientMessages' icon='email-outline'}
    {include file='admin/settings/field.tpl' type='textarea' name='processingGuide'}
    {*
        "Issued SSL Message for": a certificate family and a language. Only the boxes of the chosen family and
        language are shown (resources/assets/js/custom/issuedMessageFor.js); the two selectors have no name, so they
        are not saved.
    *}
    <span>
        <div class="tssggs-issued-for mod-field" data-savebar-ignore>
            <div class="tssggs-issued-for__field">
                <label class="mod-field-label">{t key="{$f}.issuedMessageFor"}</label>
                <select class="mod-input tssggs-issued-for__select">
                    {foreach $extra.families as $family}
                        <option value="{$family.name}">{$family.brand}</option>
                    {/foreach}
                </select>
            </div>
            <div class="tssggs-issued-for__field tssggs-issued-for__language">
                <label class="mod-field-label">{t key="{$f}.issuedMessageLanguage"}</label>
                <select class="mod-input tssggs-issued-for__language-select">
                    {foreach $extra.languages as $code => $languageName}
                        <option value="{$code}">{$languageName}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </span>
    {foreach $extra.families as $family}
        {include file='admin/settings/field.tpl' type='yesno' name=$family.switch}
        {foreach $family.texts as $text}
            {include file='admin/settings/field.tpl' type='textarea' name=$text.name label=$text.label hint=$text.hint rows=8}
        {/foreach}
    {/foreach}
    {include file='admin/settings/field.tpl' type='select' name='certificateEmailTemplate'}
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='renewals' icon='calendar-month-outline'}
    {include file='admin/settings/field.tpl' type='yesno' name='visibleRenewButtonBeforeExpiration'}
    {include file='admin/settings/field.tpl' type='yesno' name='visibleRenewButtonAfterExpiration'}
    {include file='admin/settings/field.tpl' type='yesno' name='renewOrderViaExistingOrder'}
    {include file='admin/settings/field.tpl' type='yesno' name='renewReuseOrderDetails'}
    {include file='admin/settings/field.tpl' type='select' name='additionalDaysForRenewalOrder'}
    {* the detailed descriptions with examples: the edition's Wiki *}
    <span><div class="tssggs-note">{t key="{$f}.renewalsWiki"} <a href="{$extra.wiki}" target="_blank" rel="noopener">{t key="{$f}.wikiLink"}</a></div></span>
{include file='admin/settings/section_end.tpl'}

{include file='admin/settings/section_start.tpl' name='recurringAndOneTime' icon='credit-card-clock-outline'}
    {* price of additional SANs bought after the order: every billing type *}
    {include file='admin/settings/section_start.tpl' name='allOrders' sub=true}
        {include file='admin/settings/field.tpl' type='select' name='sanUpgradePricing'}
    {include file='admin/settings/section_end.tpl'}

    {include file='admin/settings/section_start.tpl' name='recurringOrders' sub=true}
        <div x-data="{ on: {if $data.recurringCreateAutomaticRenewalInvoice|default:'0' == '1'}true{else}false{/if} }" @change="if ($event.target.name === 'recurringCreateAutomaticRenewalInvoice') { on = $event.target.value === '1' }">
            {include file='admin/settings/field.tpl' type='yesno' name='recurringCreateAutomaticRenewalInvoice'}
            {* reminders do not depend on the invoice switch: switch off + days = "reminder only" *}
            {include file='admin/settings/field.tpl' type='text' name='recurringSendExpirationNotifications'}
            <div x-show="on" x-cloak>
                {include file='admin/settings/field.tpl' type='select' name='recurringDaysBeforeExpiry'}
                {include file='admin/settings/field.tpl' type='select' name='recurringPaymentTerm'}
                {include file='admin/settings/renewal_check.tpl' check=$extra.checkCertificate}
            </div>
        </div>
    {include file='admin/settings/section_end.tpl'}

    {include file='admin/settings/section_start.tpl' name='oneTimeOrders' sub=true}
        <div x-data="{ on: {if $data.oneTimeCreateAutomaticRenewalInvoice|default:'0' == '1'}true{else}false{/if} }" @change="if ($event.target.name === 'oneTimeCreateAutomaticRenewalInvoice') { on = $event.target.value === '1' }">
            {include file='admin/settings/field.tpl' type='yesno' name='oneTimeCreateAutomaticRenewalInvoice'}
            <div x-show="on" x-cloak>
                {include file='admin/settings/field.tpl' type='select' name='oneTimeDaysBeforeExpiry'}
                {include file='admin/settings/field.tpl' type='text' name='oneTimeSendExpirationNotifications'}
            </div>
        </div>
    {include file='admin/settings/section_end.tpl'}
{include file='admin/settings/section_end.tpl'}

<script src="{$assets}/assets/js/custom/issuedMessageFor.js?v={$version}"></script>
<script src="{$assets}/assets/js/custom/renewalCheck.js?v={$version}"></script>
