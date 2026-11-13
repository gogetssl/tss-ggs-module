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
                <p class="tssggs-hero__domain">{if $csRenewal}{$MGLANG->T('csRenewalIntro')}{else}{$MGLANG->T('csConfigurationIntro')}{/if}</p>
                {if $csCurrentValidTill}<p class="tssggs-hero__domain">{$MGLANG->T('smimeCurrentValid')|sprintf:$csCurrentValidTill}</p>{/if}
            </div>
        </div>
    </section>

    <div id="tssggs-cs-alerts">
        {if $csError}<div class="alert alert-danger" role="alert">{$csError}</div>{/if}
    </div>

    {if !$csError}
    <form id="tssggs-cs-form" autocomplete="off" onsubmit="return false;" novalidate>

        {* ---------- Delivery ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="12" x="2" y="6" rx="2"/><path d="M12 12h.01M17 12h.01M7 12h.01"/></svg>
                {$MGLANG->T('csDeliverySection')}
            </h4>
            <div class="tssggs-form">
                {if $csFixedMethod}
                    <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('csDeliveryChosen')} <strong>{$csFixedLabel}{if $csShippingLabel} ({$csShippingLabel|escape}){/if}</strong></p>
                    <input type="hidden" name="cs_method" value="{$csFixedMethod|escape:'htmlall'}">
                {else}
                    {if $csRenewal}<p class="tssggs-note tssggs-note--plain">{$MGLANG->T('csRenewalInfo')}</p>{/if}
                    <div class="tssggs-field">
                        <label>{$MGLANG->T('csDeliveryLabel')} <span class="tssggs-req">*</span></label>
                        {foreach from=$csMethods key=method item=label name=methods}
                            <label class="tssggs-check">
                                <input type="radio" name="cs_method" value="{$method|escape:'htmlall'}" required{if ($csPreviousMethod && $method == $csPreviousMethod) || (!$csPreviousMethod && $smarty.foreach.methods.first)} checked{/if}>
                                <span>{$label}</span>
                            </label>
                        {/foreach}
                    </div>
                {/if}

                <div class="tssggs-field" data-tssggs-cs-for="ship-token">
                    <p class="tssggs-note">{$MGLANG->T('csShipTokenHint')}</p>
                </div>
                <div class="tssggs-field" data-tssggs-cs-for="existing-token">
                    <label for="tssggs-cs-model">{$MGLANG->T('csTokenModelLabel')} <span class="tssggs-req">*</span></label>
                    <select id="tssggs-cs-model" name="cs_model" class="form-control">
                        <option value="">-</option>
                        {foreach from=$csTokenModels key=code item=name}<option value="{$code|escape:'htmlall'}">{$name|escape}</option>{/foreach}
                    </select>
                    <small>{$MGLANG->T('csTokenModelHint')}</small>
                </div>
                <div class="tssggs-field" data-tssggs-cs-for="hsm">
                    <label for="tssggs-cs-csr">{$MGLANG->T('csCsrLabel')} <span class="tssggs-req">*</span></label>
                    <textarea id="tssggs-cs-csr" name="cs_csr" class="form-control tssggs-mono" rows="6" placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>
                    <small>{$MGLANG->T('csCsrHint')}</small>
                </div>
                {if $csHsmModels}
                    <div class="tssggs-field" data-tssggs-cs-for="hsm">
                        <label for="tssggs-cs-hsm-model">{$MGLANG->T('csHsmModelLabel')} <span class="tssggs-req">*</span></label>
                        <select id="tssggs-cs-hsm-model" name="cs_hsm_model" class="form-control" required>
                            <option value="">-</option>
                            {foreach from=$csHsmModels key=code item=name}<option value="{$code|escape:'htmlall'}">{$name|escape}</option>{/foreach}
                        </select>
                    </div>
                    <div class="tssggs-field" data-tssggs-cs-for="hsm">
                        <label for="tssggs-cs-attestation">{$MGLANG->T('csAttestationLabel')} <span class="tssggs-req">*</span></label>
                        <textarea id="tssggs-cs-attestation" name="cs_attestation" class="form-control tssggs-mono" rows="4" required></textarea>
                        <small>{$MGLANG->T('csAttestationHint')}</small>
                    </div>
                {/if}
            </div>
        </section>

        {* ---------- Organization ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                {$MGLANG->T('csOrganizationSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('csOrganizationHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-cs-org-name">{$MGLANG->T('csOrg_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-org-name" name="org[name]" class="form-control" required value="{$csClient.companyname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-phone">{$MGLANG->T('csOrg_phone')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-org-phone" name="org[phone]" class="form-control" required value="{$csClient.phonenumber|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-address1">{$MGLANG->T('csOrg_address_line_1')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-org-address1" name="org[address_line_1]" class="form-control" required value="{$csClient.address1|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-address2">{$MGLANG->T('csOrg_address_line_2')}</label><input type="text" id="tssggs-cs-org-address2" name="org[address_line_2]" class="form-control" value="{$csClient.address2|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-city">{$MGLANG->T('csOrg_city')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-org-city" name="org[city]" class="form-control" required value="{$csClient.city|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-region">{$MGLANG->T('csOrg_region')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-org-region" name="org[region]" class="form-control" required value="{$csClient.state|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-postal">{$MGLANG->T('csOrg_postal_code')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-org-postal" name="org[postal_code]" class="form-control" required value="{$csClient.postcode|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-org-country">{$MGLANG->T('csOrg_country')} <span class="tssggs-req">*</span></label>
                        {if $csCountries}
                            <select id="tssggs-cs-org-country" name="org[country]" class="form-control" required>
                                {foreach from=$csCountries key=code item=name}<option value="{$code|escape:'htmlall'}"{if $code == $csClient.country} selected{/if}>{$name|escape}</option>{/foreach}
                            </select>
                        {else}
                            <input type="text" id="tssggs-cs-org-country" name="org[country]" class="form-control" required maxlength="2" value="{$csClient.country|escape:'htmlall'}">
                        {/if}
                    </div>
                </div>
            </div>
        </section>

        {* ---------- Administrator ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {$MGLANG->T('csAdministratorSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('csAdministratorHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-cs-admin-first">{$MGLANG->T('csAdmin_first_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-admin-first" name="admin[first_name]" class="form-control" required value="{$csClient.firstname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-admin-last">{$MGLANG->T('csAdmin_last_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-admin-last" name="admin[last_name]" class="form-control" required value="{$csClient.lastname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-admin-title">{$MGLANG->T('csAdmin_title')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-admin-title" name="admin[title]" class="form-control" required></div>
                    <div class="tssggs-field"><label for="tssggs-cs-admin-email">{$MGLANG->T('csAdmin_email')} <span class="tssggs-req">*</span></label><input type="email" id="tssggs-cs-admin-email" name="admin[email]" class="form-control" required value="{$csClient.email|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-cs-admin-phone">{$MGLANG->T('csAdmin_phone')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-cs-admin-phone" name="admin[phone]" class="form-control" required value="{$csClient.phonenumber|escape:'htmlall'}"></div>
                </div>
                <label class="tssggs-check">
                    <input type="checkbox" name="admin[same_address]" value="1" checked data-tssggs-cs-toggle="admin">
                    <span>{$MGLANG->T('csAdminSameAddress')}</span>
                </label>
                <div class="tssggs-fields" data-tssggs-cs-address="admin" hidden>
                    <div class="tssggs-field"><label>{$MGLANG->T('csAdmin_address_line_1')}</label><input type="text" name="admin[address_line_1]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csAdmin_address_line_2')}</label><input type="text" name="admin[address_line_2]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csAdmin_city')}</label><input type="text" name="admin[city]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csAdmin_region')}</label><input type="text" name="admin[region]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csAdmin_postal_code')}</label><input type="text" name="admin[postal_code]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csAdmin_country')}</label><input type="text" name="admin[country]" class="form-control" maxlength="2"></div>
                </div>
            </div>
        </section>

        {* ---------- Token shipping address (new token) ---------- *}
        <section class="tssggs-card" data-tssggs-cs-for="ship-token">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2M15 18H9M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                {$MGLANG->T('csShippingSection')}
            </h4>
            <div class="tssggs-form">
                <label class="tssggs-check">
                    <input type="checkbox" name="ship[same_address]" value="1" checked data-tssggs-cs-toggle="ship">
                    <span>{$MGLANG->T('csShipSameAddress')}</span>
                </label>
                <div class="tssggs-fields" data-tssggs-cs-address="ship" hidden>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_first_name')}</label><input type="text" name="ship[first_name]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_last_name')}</label><input type="text" name="ship[last_name]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_address_line_1')}</label><input type="text" name="ship[address_line_1]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_address_line_2')}</label><input type="text" name="ship[address_line_2]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_city')}</label><input type="text" name="ship[city]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_region')}</label><input type="text" name="ship[region]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_postal_code')}</label><input type="text" name="ship[postal_code]" class="form-control"></div>
                    <div class="tssggs-field"><label>{$MGLANG->T('csShip_country')}</label><input type="text" name="ship[country]" class="form-control" maxlength="2"></div>
                </div>
            </div>
        </section>

        <p class="tssggs-note tssggs-note--block">{$MGLANG->T('csValidationInfo')}</p>

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
            <button id="tssggs-cs-submit" type="button" class="tssggs-btn tssggs-btn--primary">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{$MGLANG->T('csSubmitOrder')}</span>
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
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
        tssggsCsSync();
    });

    // shows the parts of the chosen delivery method and the own-address fields
    function tssggsCsSync() {
        var form = $('#tssggs-cs-form');
        var method = form.find('[name="cs_method"]:checked').val() || form.find('input[type="hidden"][name="cs_method"]').val() || '';
        form.find('[data-tssggs-cs-for]').each(function () {
            var off = $(this).attr('data-tssggs-cs-for') !== method;
            $(this).prop('hidden', off).find('[required]').prop('disabled', off);
        });
        form.find('[data-tssggs-cs-toggle]').each(function () {
            form.find('[data-tssggs-cs-address="' + $(this).attr('data-tssggs-cs-toggle') + '"]').prop('hidden', $(this).is(':checked'));
        });
    }
    $(document).on('change', '#tssggs-cs-form [name="cs_method"], #tssggs-cs-form [data-tssggs-cs-toggle]', tssggsCsSync);

    $(document).on('click', '#tssggs-cs-submit', function () {
        var form   = document.getElementById('tssggs-cs-form');
        var alerts = $('#tssggs-cs-alerts');
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
        data.push({ldelim}name: 'mg-action', value: 'createCodeSigning'{rdelim});
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
            alerts.html($('<div class="alert alert-success" role="alert"></div>').text((payload.data && payload.data.message) || '{$MGLANG->T('csOrderedSuccessfully')|escape:'javascript'}'));
            window.setTimeout(function () {
                window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
            }, 1200);
        }).fail(function () {
            fail('{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}');
        });
    });
</script>
{/if}
