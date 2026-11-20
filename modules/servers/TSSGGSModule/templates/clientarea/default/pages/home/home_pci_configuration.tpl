{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4M12 16h.01"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$MGLANG->T('smimeAwaitingOrder')}</span>
                <h3>{$productName|escape}</h3>
                <p class="tssggs-hero__domain">{$MGLANG->T('pciConfigurationIntro')}</p>
            </div>
        </div>
    </section>

    <div id="tssggs-pci-alerts"></div>

    <form id="tssggs-pci-form" autocomplete="off" onsubmit="return false;" novalidate>

        {* ---------- Organization ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                {$MGLANG->T('dsOrganizationSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('pciOrganizationHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-pci-org-name">{$MGLANG->T('dsOrg_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-org-name" name="org[name]" class="form-control" required value="{$smimeClient.companyname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-phone">{$MGLANG->T('dsOrg_phone')}</label><input type="text" id="tssggs-pci-org-phone" name="org[phone]" class="form-control" value="{$smimeClient.phonenumber|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-address1">{$MGLANG->T('dsOrg_address_line_1')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-org-address1" name="org[address_line_1]" class="form-control" required value="{$smimeClient.address1|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-address2">{$MGLANG->T('dsOrg_address_line_2')}</label><input type="text" id="tssggs-pci-org-address2" name="org[address_line_2]" class="form-control" value="{$smimeClient.address2|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-city">{$MGLANG->T('dsOrg_city')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-org-city" name="org[city]" class="form-control" required value="{$smimeClient.city|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-region">{$MGLANG->T('dsOrg_region')}</label><input type="text" id="tssggs-pci-org-region" name="org[region]" class="form-control" value="{$smimeClient.state|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-postal">{$MGLANG->T('dsOrg_postal_code')}</label><input type="text" id="tssggs-pci-org-postal" name="org[postal_code]" class="form-control" value="{$smimeClient.postcode|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-org-country">{$MGLANG->T('dsOrg_country')} <span class="tssggs-req">*</span></label>
                        {if $smimeCountries}
                            <select id="tssggs-pci-org-country" name="org[country]" class="form-control" required>
                                {foreach from=$smimeCountries key=code item=name}<option value="{$code|escape:'htmlall'}"{if $code == $smimeClient.country} selected{/if}>{$name|escape}</option>{/foreach}
                            </select>
                        {else}
                            <input type="text" id="tssggs-pci-org-country" name="org[country]" class="form-control" required maxlength="2" value="{$smimeClient.country|escape:'htmlall'}">
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
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('pciAdministratorHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-pci-admin-first">{$MGLANG->T('dsAdmin_first_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-admin-first" name="admin[first_name]" class="form-control" required value="{$smimeClient.firstname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-admin-last">{$MGLANG->T('dsAdmin_last_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-admin-last" name="admin[last_name]" class="form-control" required value="{$smimeClient.lastname|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-admin-title">{$MGLANG->T('dsAdmin_title')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-admin-title" name="admin[title]" class="form-control" required></div>
                    <div class="tssggs-field"><label for="tssggs-pci-admin-email">{$MGLANG->T('dsAdmin_email')} <span class="tssggs-req">*</span></label><input type="email" id="tssggs-pci-admin-email" name="admin[email]" class="form-control" required value="{$smimeClient.email|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-pci-admin-phone">{$MGLANG->T('dsAdmin_phone')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-pci-admin-phone" name="admin[phone]" class="form-control" required value="{$smimeClient.phonenumber|escape:'htmlall'}"></div>
                </div>
            </div>
        </section>

        <p class="tssggs-note tssggs-note--block">{$MGLANG->T('pciOrderInfo')}</p>

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
            <button id="tssggs-pci-submit" type="button" class="tssggs-btn tssggs-btn--primary">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{$MGLANG->T('pciSubmitOrder')}</span>
            </button>
        </div>
    </form>
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
    });

    $(document).on('click', '#tssggs-pci-submit', function () {
        var form   = document.getElementById('tssggs-pci-form');
        var alerts = $('#tssggs-pci-alerts');
        var btn    = $(this);
        var label  = btn.find('span');
        var text   = label.text();

        alerts.html('');
        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        var data = $(form).serializeArray();
        data.push({ldelim}name: 'mg-action', value: 'createPci'{rdelim});
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
            alerts.html($('<div class="alert alert-success" role="alert"></div>').text((payload.data && payload.data.message) || '{$MGLANG->T('pciOrderedSuccessfully')|escape:'javascript'}'));
            window.setTimeout(function () {
                window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
            }, 1200);
        }).fail(function () {
            fail('{$MGLANG->T('smimeErrorOccurred')|escape:'javascript'}');
        });
    });
</script>
{/if}
