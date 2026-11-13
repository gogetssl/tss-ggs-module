{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="m18 16 4-4-4-4M6 8l-4 4 4 4M14.5 4l-5 16"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$MGLANG->T('smimeAwaitingOrder')}</span>
                <h3>{$productName|escape}</h3>
                <p class="tssggs-hero__domain">{if $dsRenewal}{$MGLANG->T('dsRenewalIntro')}{else}{$MGLANG->T('dsConfigurationIntro')}{/if}</p>
                {if $dsCurrentValidTill}<p class="tssggs-hero__domain">{$MGLANG->T('smimeCurrentValid')|sprintf:$dsCurrentValidTill}</p>{/if}
            </div>
        </div>
    </section>

    <div id="tssggs-ds-alerts">
        {if $dsError}<div class="alert alert-danger" role="alert">{$dsError}</div>{/if}
    </div>

    {if !$dsError}
    <form id="tssggs-ds-form" autocomplete="off" onsubmit="return false;" novalidate>

        {* ---------- Delivery ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="12" x="2" y="6" rx="2"/><path d="M12 12h.01M17 12h.01M7 12h.01"/></svg>
                {$MGLANG->T('dsDeliverySection')}
            </h4>
            <div class="tssggs-form">
                    {if $dsRenewal}<p class="tssggs-note tssggs-note--plain">{$MGLANG->T('dsRenewalInfo')}</p>{/if}
                    <div class="tssggs-field">
                        <label>{$MGLANG->T('dsDeliveryLabel')} <span class="tssggs-req">*</span></label>
                        {foreach from=$dsMethods key=method item=label name=methods}
                            <label class="tssggs-check">
                                <input type="radio" name="ds_method" value="{$method|escape:'htmlall'}" required{if ($dsPreviousMethod && $method == $dsPreviousMethod) || (!$dsPreviousMethod && $smarty.foreach.methods.first)} checked{/if}>
                                <span>{$label}</span>
                            </label>
                        {/foreach}
                    </div>

                <div class="tssggs-field" data-tssggs-ds-for="ship-token">
                    <p class="tssggs-note">{$MGLANG->T('dsShipTokenHint')}</p>
                    {if $dsShippingOptions|@count > 1}
                        <label for="tssggs-ds-shipping">{$MGLANG->T('dsShippingLabel')} <span class="tssggs-req">*</span></label>
                        <select id="tssggs-ds-shipping" name="ds_shipping" class="form-control">
                            {foreach from=$dsShippingOptions item=option}<option value="{$option.id|escape:'htmlall'}">{$option.label|escape}</option>{/foreach}
                        </select>
                    {else}
                        <input type="hidden" name="ds_shipping" value="{if $dsShippingOptions}{$dsShippingOptions[0].id|escape:'htmlall'}{else}STANDARD{/if}">
                    {/if}
                </div>
                <div class="tssggs-field" data-tssggs-ds-for="existing-token">
                    <label for="tssggs-ds-model">{$MGLANG->T('dsTokenModelLabel')} <span class="tssggs-req">*</span></label>
                    <select id="tssggs-ds-model" name="ds_model" class="form-control">
                        <option value="">-</option>
                        {foreach from=$dsModels key=code item=name}<option value="{$code|escape:'htmlall'}">{$name|escape}</option>{/foreach}
                    </select>
                    <small>{$MGLANG->T('dsTokenModelHint')}</small>
                </div>
            </div>
        </section>

        {* ---------- Organization ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                {$MGLANG->T('dsOrganizationSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('dsOrganizationHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-ds-org-name">{$MGLANG->T('dsOrg_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-org-name" name="org[name]" class="form-control" required value="{$smimeClient.companyname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-phone">{$MGLANG->T('dsOrg_phone')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-org-phone" name="org[phone]" class="form-control" required value="{$smimeClient.phonenumber|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-address1">{$MGLANG->T('dsOrg_address_line_1')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-org-address1" name="org[address_line_1]" class="form-control" required value="{$smimeClient.address1|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-address2">{$MGLANG->T('dsOrg_address_line_2')}</label><input type="text" id="tssggs-ds-org-address2" name="org[address_line_2]" class="form-control" value="{$smimeClient.address2|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-city">{$MGLANG->T('dsOrg_city')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-org-city" name="org[city]" class="form-control" required value="{$smimeClient.city|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-region">{$MGLANG->T('dsOrg_region')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-org-region" name="org[region]" class="form-control" required value="{$smimeClient.state|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-postal">{$MGLANG->T('dsOrg_postal_code')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-org-postal" name="org[postal_code]" class="form-control" required value="{$smimeClient.postcode|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-org-country">{$MGLANG->T('dsOrg_country')} <span class="tssggs-req">*</span></label>
                        {if $smimeCountries}
                            <select id="tssggs-ds-org-country" name="org[country]" class="form-control" required>
                                {foreach from=$smimeCountries key=code item=name}<option value="{$code|escape:'htmlall'}"{if $code == $smimeClient.country} selected{/if}>{$name|escape}</option>{/foreach}
                            </select>
                        {else}
                            <input type="text" id="tssggs-ds-org-country" name="org[country]" class="form-control" required maxlength="2" value="{$smimeClient.country|escape:'htmlall'}">
                        {/if}
                    </div>
                </div>
            </div>
        </section>

        {* ---------- Administrator ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {$MGLANG->T('dsAdministratorSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('dsAdministratorHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-ds-admin-first">{$MGLANG->T('dsAdmin_first_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-admin-first" name="admin[first_name]" class="form-control" required value="{$smimeClient.firstname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-admin-last">{$MGLANG->T('dsAdmin_last_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-admin-last" name="admin[last_name]" class="form-control" required value="{$smimeClient.lastname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-admin-title">{$MGLANG->T('dsAdmin_title')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-admin-title" name="admin[title]" class="form-control" required></div>
                    <div class="tssggs-field"><label for="tssggs-ds-admin-email">{$MGLANG->T('dsAdmin_email')} <span class="tssggs-req">*</span></label><input type="email" id="tssggs-ds-admin-email" name="admin[email]" class="form-control" required value="{$smimeClient.email|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-ds-admin-phone">{$MGLANG->T('dsAdmin_phone')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-ds-admin-phone" name="admin[phone]" class="form-control" required value="{$smimeClient.phonenumber|escape:'htmlall'}"></div>
                </div>
                <label class="tssggs-check">
                    <input type="checkbox" name="admin[same_address]" value="1" checked data-tssggs-ds-toggle="admin">
                    <span>{$MGLANG->T('dsAdminSameAddress')}</span>
                </label>
                <div class="tssggs-fields" data-tssggs-ds-address="admin" hidden>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsAdmin_address_line_1')}</label><input type="text" name="admin[address_line_1]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsAdmin_address_line_2')}</label><input type="text" name="admin[address_line_2]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsAdmin_city')}</label><input type="text" name="admin[city]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsAdmin_region')}</label><input type="text" name="admin[region]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsAdmin_postal_code')}</label><input type="text" name="admin[postal_code]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsAdmin_country')}</label><input type="text" name="admin[country]" class="form-control" maxlength="2"></div>
                </div>
            </div>
        </section>

        {* ---------- Token shipping address (new token) ---------- *}
        <section class="tssggs-card" data-tssggs-ds-for="ship-token">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2M15 18H9M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                {$MGLANG->T('dsShippingSection')}
            </h4>
            <div class="tssggs-form">
                <label class="tssggs-check">
                    <input type="checkbox" name="ship[same_address]" value="1" checked data-tssggs-ds-toggle="ship">
                    <span>{$MGLANG->T('dsShipSameAddress')}</span>
                </label>
                <div class="tssggs-fields" data-tssggs-ds-address="ship" hidden>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_first_name')}</label><input type="text" name="ship[first_name]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_last_name')}</label><input type="text" name="ship[last_name]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_address_line_1')}</label><input type="text" name="ship[address_line_1]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_address_line_2')}</label><input type="text" name="ship[address_line_2]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_city')}</label><input type="text" name="ship[city]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_region')}</label><input type="text" name="ship[region]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_postal_code')}</label><input type="text" name="ship[postal_code]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('dsShip_country')}</label><input type="text" name="ship[country]" class="form-control" maxlength="2"></div>
                </div>
            </div>
        </section>

        <p class="tssggs-note tssggs-note--block">{$MGLANG->T('dsValidationInfo')}</p>

        {if $agreement}
            <section class="tssggs-card">
                <div class="tssggs-form tssggs-agreement">
                    {if $agreement.text}<p class="tssggs-note tssggs-note--plain">{$agreement.text}</p>{/if}
                    <label class="tssggs-check">
                        <input type="checkbox" name="agreement" value="1" required>
                        <span>{$MGLANG->T('sslConfigAgreement')} {if $agreement.url}<a href="{$agreement.url|escape}" target="_blank" rel="noopener">{$agreement.name|escape}</a>{else}{$agreement.name|escape}{/if}</span>
                    </label>
                </div>
            </section>
        {/if}

        <div class="tssggs-actions tssggs-actions--end">
            <button id="tssggs-ds-submit" type="button" class="tssggs-btn tssggs-btn--primary">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{$MGLANG->T('dsSubmitOrder')}</span>
            </button>
        </div>
    </form>
    {/if}
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mg-module .validate-ssl-unified').remove();
        tssggsDsSync();
    });

    // shows the parts of the chosen delivery method and the own-address fields
    function tssggsDsSync() {
        var form = $('#tssggs-ds-form');
        var method = form.find('[name="ds_method"]:checked').val() || form.find('input[type="hidden"][name="ds_method"]').val() || '';
        form.find('[data-tssggs-ds-for]').each(function () {
            var off = $(this).attr('data-tssggs-ds-for') !== method;
            $(this).prop('hidden', off).find('[required]').prop('disabled', off);
        });
        form.find('[data-tssggs-ds-toggle]').each(function () {
            form.find('[data-tssggs-ds-address="' + $(this).attr('data-tssggs-ds-toggle') + '"]').prop('hidden', $(this).is(':checked'));
        });
    }
    $(document).on('change', '#tssggs-ds-form [name="ds_method"], #tssggs-ds-form [data-tssggs-ds-toggle]', tssggsDsSync);

    $(document).on('click', '#tssggs-ds-submit', function () {
        var form   = document.getElementById('tssggs-ds-form');
        var alerts = $('#tssggs-ds-alerts');
        var btn    = $(this);
        var label  = btn.find('span');
        var text   = label.text();

        alerts.html('');
        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        // only the fields of the chosen delivery and the visible address blocks
        var data = $(form).find(':input').filter(function () {
            return !$(this).closest('[hidden]').length;
        }).serializeArray();
        data.push({ldelim}name: 'mg-action', value: 'createDocumentSigning'{rdelim});
        data.push({ldelim}name: 'token', value: '{$csrfToken}'{rdelim});

        function fail(message) {
            alerts.html($('<div class="alert alert-danger" role="alert"></div>').html(message));
            btn.prop('disabled', false);
            label.text(text);
            window.scrollTo(0, alerts.offset().top - 90);
        }

        btn.prop('disabled', true);
        label.text('{$MGLANG->T('smimeOrdering')|escape:'javascript'}');

        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', $.param(data), function (ret) {
            var payload;
            try {
                payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
            } catch (e) {
                fail('{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}');
                return;
            }
            if (payload.result === 'error') {
                fail(payload.error || '{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}');
                return;
            }
            alerts.html($('<div class="alert alert-success" role="alert"></div>').text((payload.data && payload.data.message) || '{$MGLANG->T('dsOrderedSuccessfully')|escape:'javascript'}'));
            window.setTimeout(function () {
                window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
            }, 1200);
        }).fail(function () {
            fail('{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}');
        });
    });
</script>
{/if}
