{if $allOk === true}
<div class="validate-ssl-unified tssggs-cert tssggs-smime">

    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{$text->t('smimeAwaitingOrder')}</span>
                <h3>{$productName|escape}</h3>
                <p class="tssggs-hero__domain">{if $leiRenewal}{$text->t('leiRenewalIntro')}{else}{$text->t('leiConfigurationIntro')}{/if}</p>
                {if $leiRenewal}<p class="tssggs-hero__domain tssggs-mono">LEI {$leiRenewNumber|escape}{if $leiRenewName} &middot; {$leiRenewName|escape}{/if}</p>{/if}
            </div>
        </div>
    </section>

    <div id="tssggs-lei-alerts"></div>

    <form id="tssggs-lei-form" autocomplete="off" onsubmit="return false;" novalidate>
        <input type="hidden" name="lei_mode" value="{if $leiRenewal}renew{else}new{/if}">

        {if $leiRenewal}
        {* ---------- Renewal of the issued LEI ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                {$text->t('leiRenewalSection')}
            </h4>
            <div class="tssggs-form">
                {if $leiRenewTill}<p class="tssggs-note tssggs-note--plain">{$text->t('leiRenewalValidTill')|sprintf:$leiRenewTill}</p>{/if}
                <label class="tssggs-check">
                    <input type="checkbox" name="level1_same" value="1" checked>
                    <span><strong>{$text->t('leiLevel1Same')}</strong><br><small>{$text->t('leiLevel1SameHint')}</small></span>
                </label>
            </div>
        </section>
        {else}
        {* ---------- Company search ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                {$text->t('leiSearchSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{if $leiOpenCorporates}{$text->t('leiSearchHint')}{else}{$text->t('leiSearchHintGleif')}{/if}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-lei-q">{$text->t('leiSearchQuery')}</label><input type="text" id="tssggs-lei-q" class="form-control" value="{$leiForm.legal_name|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-qc">{$text->t('leiField_legal_address_country')}</label>
                        {if $smimeCountries}
                            <select id="tssggs-lei-qc" class="form-control">
                                <option value="">-</option>
                                {foreach from=$smimeCountries key=code item=name}<option value="{$code|escape:'htmlall'}"{if $code == $leiForm.country} selected{/if}>{$name|escape}</option>{/foreach}
                            </select>
                        {else}
                            <input type="text" id="tssggs-lei-qc" class="form-control" maxlength="2" value="{$leiForm.country|escape:'htmlall'}">
                        {/if}
                    </div>
                </div>
                <div class="tssggs-actions">
                    <button id="tssggs-lei-search" type="button" class="tssggs-btn">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <span>{$text->t('leiSearchButton')}</span>
                    </button>
                </div>
                <div id="tssggs-lei-results"></div>
            </div>
        </section>

        {* ---------- Legal entity ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                {$text->t('leiEntitySection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$text->t('leiEntityHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-lei-name">{$text->t('leiField_legal_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-name" name="entity[legal_name]" class="form-control" required maxlength="150" value="{$leiForm.legal_name|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-reg">{$text->t('leiField_registration_authority_entity_id')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-reg" name="entity[registration_authority_entity_id]" class="form-control" required maxlength="50" value="{$leiForm.registration|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-jur">{$text->t('leiField_legal_jurisdiction')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-jur" name="entity[legal_jurisdiction]" class="form-control" required maxlength="6" value="{$leiForm.jurisdiction|escape:'htmlall'}"><small>{$text->t('leiJurisdictionHint')}</small></div>
                    <div class="tssggs-field"><label for="tssggs-lei-inc">{$text->t('leiField_incorporation_date')}</label><input type="date" id="tssggs-lei-inc" name="entity[incorporation_date]" class="form-control" value="{$leiForm.incorporation_date|escape:'htmlall'}"></div>
                </div>
                <h5>{$text->t('leiLegalAddress')}</h5>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-lei-a1">{$text->t('leiField_legal_address_line_1')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-a1" name="entity[legal_address][address_line_1]" class="form-control" required maxlength="100" value="{$leiForm.address_line_1|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-city">{$text->t('leiField_legal_city')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-city" name="entity[legal_address][city]" class="form-control" required maxlength="50" value="{$leiForm.city|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-region">{$text->t('leiField_legal_region')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-region" name="entity[legal_address][region]" class="form-control" required maxlength="50" value="{$leiForm.region|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-postal">{$text->t('leiField_legal_postal_code')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-postal" name="entity[legal_address][postal_code]" class="form-control" required maxlength="50" value="{$leiForm.postal_code|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-country">{$text->t('leiField_legal_country')} <span class="tssggs-req">*</span></label>
                        {if $smimeCountries}
                            <select id="tssggs-lei-country" name="entity[legal_address][country]" class="form-control" required>
                                {foreach from=$smimeCountries key=code item=name}<option value="{$code|escape:'htmlall'}"{if $code == $leiForm.country} selected{/if}>{$name|escape}</option>{/foreach}
                            </select>
                        {else}
                            <input type="text" id="tssggs-lei-country" name="entity[legal_address][country]" class="form-control" required maxlength="2" value="{$leiForm.country|escape:'htmlall'}">
                        {/if}
                    </div>
                </div>
                <label class="tssggs-check">
                    <input type="checkbox" name="entity[hq_differs]" value="1" data-tssggs-lei-toggle="hq">
                    <span>{$text->t('leiHqDiffers')}</span>
                </label>
                <div class="tssggs-fields" data-tssggs-lei-block="hq" hidden>
                    <div class="tssggs-field"><label>{$text->t('leiField_hq_address_line_1')} <span class="tssggs-req">*</span></label><input type="text" name="entity[headquarters_address][address_line_1]" class="form-control" required maxlength="100"></div>
                    <div class="tssggs-field"><label>{$text->t('leiField_hq_city')} <span class="tssggs-req">*</span></label><input type="text" name="entity[headquarters_address][city]" class="form-control" required maxlength="50"></div>
                    <div class="tssggs-field"><label>{$text->t('leiField_hq_region')} <span class="tssggs-req">*</span></label><input type="text" name="entity[headquarters_address][region]" class="form-control" maxlength="50"></div>
                    <div class="tssggs-field"><label>{$text->t('leiField_hq_postal_code')} <span class="tssggs-req">*</span></label><input type="text" name="entity[headquarters_address][postal_code]" class="form-control" required maxlength="50"></div>
                    <div class="tssggs-field"><label>{$text->t('leiField_hq_country')} <span class="tssggs-req">*</span></label>
                        {if $smimeCountries}
                            <select name="entity[headquarters_address][country]" class="form-control" required>
                                {foreach from=$smimeCountries key=code item=name}<option value="{$code|escape:'htmlall'}"{if $code == $leiForm.country} selected{/if}>{$name|escape}</option>{/foreach}
                            </select>
                        {else}
                            <input type="text" name="entity[headquarters_address][country]" class="form-control" required maxlength="2">
                        {/if}
                    </div>
                </div>
                <h5>{$text->t('leiLevel2Section')}</h5>
                <label class="tssggs-check">
                    <input type="checkbox" name="entity[level2_available]" value="1" data-tssggs-lei-toggle="level2">
                    <span>{$text->t('leiLevel2Available')}</span>
                </label>
                <div data-tssggs-lei-block="level2" hidden style="margin-left:24px;">
                    <label class="tssggs-check"><input type="checkbox" name="entity[level2_consolidated]" value="1"><span>{$text->t('leiLevel2Consolidated')}</span></label>
                    <label class="tssggs-check"><input type="checkbox" name="entity[level2_ultimate]" value="1"><span>{$text->t('leiLevel2Ultimate')}</span></label>
                </div>
            </div>
        </section>
        {/if}

        {* ---------- Signing authority ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {$text->t('leiSigningSection')}
            </h4>
            <div class="tssggs-form">
                <p class="tssggs-note tssggs-note--plain">{$text->t('leiSigningHint')}</p>
                <div class="tssggs-fields">
                    <div class="tssggs-field"><label for="tssggs-lei-first">{$text->t('leiField_signing_first_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-first" name="signing[first_name]" class="form-control" required maxlength="50" value="{$leiForm.first_name|escape:'htmlall'}"></div>
                    <div class="tssggs-field"><label for="tssggs-lei-last">{$text->t('leiField_signing_last_name')} <span class="tssggs-req">*</span></label><input type="text" id="tssggs-lei-last" name="signing[last_name]" class="form-control" required maxlength="50" value="{$leiForm.last_name|escape:'htmlall'}"></div>
                </div>
                <label class="tssggs-check">
                    <input type="checkbox" name="lei_confirm" value="1" required>
                    <span>{$text->t('leiConfirm')}</span>
                </label>
            </div>
        </section>

        <p class="tssggs-note tssggs-note--block">{$text->t('leiValidationInfo')}</p>

        <div class="tssggs-actions tssggs-actions--end">
            <button id="tssggs-lei-submit" type="button" class="tssggs-btn tssggs-btn--primary">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{if $leiRenewal}{$text->t('leiSubmitRenewal')}{else}{$text->t('leiSubmitOrder')}{/if}</span>
            </button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var clone = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone);
        $('.mod-module .validate-ssl-unified').remove();
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
        tssggsLeiSync();
    });

    // the headquarters address and level 2 flags show only when ticked; hidden required fields are not checked
    function tssggsLeiSync() {
        var form = $('#tssggs-lei-form');
        form.find('[data-tssggs-lei-toggle]').each(function () {
            var block = form.find('[data-tssggs-lei-block="' + $(this).attr('data-tssggs-lei-toggle') + '"]');
            var on = $(this).is(':checked');
            block.prop('hidden', !on).find(':input').prop('disabled', !on);
        });
    }
    $(document).on('change', '#tssggs-lei-form [data-tssggs-lei-toggle]', tssggsLeiSync);

    function tssggsLeiPost(data, done, fail) {
        $.post('clientarea.php?action=productdetails&id={$serviceid}&json=1', $.param(data), function (ret) {
            var payload;
            try {
                payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
            } catch (e) {
                fail('{$text->t('smimeErrorOccurred')|escape:'javascript'}');
                return;
            }
            if (payload.result === 'error') {
                fail(payload.error || '{$text->t('smimeErrorOccurred')|escape:'javascript'}');
                return;
            }
            done(payload.data || {ldelim}{rdelim});
        }).fail(function () {
            fail('{$text->t('smimeErrorOccurred')|escape:'javascript'}');
        });
    }

    // company search: a result fills in the legal entity
    var tssggsLeiFound = [];
    $(document).on('click', '#tssggs-lei-search', function () {
        var btn = $(this), box = $('#tssggs-lei-results');
        btn.prop('disabled', true);
        box.html('<p class="tssggs-note tssggs-note--plain">{$text->t('leiSearching')|escape:'javascript'}</p>');
        tssggsLeiPost([
            {ldelim}name: 'mod-action', value: 'searchLeiCompany'{rdelim},
            {ldelim}name: 'token', value: '{$csrfToken}'{rdelim},
            {ldelim}name: 'query', value: $('#tssggs-lei-q').val(){rdelim},
            {ldelim}name: 'country', value: $('#tssggs-lei-qc').val()|| ''{rdelim}
        ], function (data) {
            btn.prop('disabled', false);
            tssggsLeiFound = data.results || [];
            box.html('');
            if (!tssggsLeiFound.length) {
                box.html($('<p class="tssggs-note tssggs-note--plain"></p>').text('{$text->t('leiSearchNone')|escape:'javascript'}'));
                return;
            }
            var list = $('<div class="list-group" style="margin-top:10px;"></div>');
            $.each(tssggsLeiFound, function (i, r) {
                var item = $('<button type="button" class="list-group-item" style="text-align:left;"></button>').attr('data-tssggs-lei-pick', i);
                item.append($('<strong></strong>').text(r.legal_name));
                var meta = [r.registration_number, r.jurisdiction, [r.address.address_line_1, r.address.city].filter(Boolean).join(', ')].filter(Boolean).join(' · ');
                item.append('<br>').append($('<small></small>').text(meta));
                if (r.lei) {
                    item.append('<br>').append($('<small class="text-warning"></small>').text('{$text->t('leiSearchHasLei')|escape:'javascript'}'.replace('%s', r.lei + (r.lei_status ? ' (' + r.lei_status + ')' : ''))));
                }
                list.append(item);
            });
            box.append(list);
        }, function (message) {
            btn.prop('disabled', false);
            box.html($('<div class="alert alert-danger" role="alert"></div>').html(message));
        });
    });
    $(document).on('click', '[data-tssggs-lei-pick]', function () {
        var r = tssggsLeiFound[parseInt($(this).attr('data-tssggs-lei-pick'), 10)];
        if (!r) {
            return;
        }
        var set = function (name, value) {
            if (value) {
                $('#tssggs-lei-form [name="' + name + '"]').val(value);
            }
        };
        set('entity[legal_name]', r.legal_name);
        set('entity[registration_authority_entity_id]', r.registration_number);
        set('entity[legal_jurisdiction]', r.jurisdiction);
        set('entity[incorporation_date]', r.incorporation_date);
        set('entity[legal_address][address_line_1]', r.address.address_line_1);
        set('entity[legal_address][city]', r.address.city);
        $('#tssggs-lei-form [name="entity[legal_address][region]"]').val(r.address.region || r.address.city || '');
        set('entity[legal_address][postal_code]', r.address.postal_code);
        set('entity[legal_address][country]', r.address.country);
        $('[data-tssggs-lei-pick]').removeClass('active');
        $(this).addClass('active');
    });

    $(document).on('click', '#tssggs-lei-submit', function () {
        var form   = document.getElementById('tssggs-lei-form');
        var alerts = $('#tssggs-lei-alerts');
        var btn    = $(this);
        var label  = btn.find('span');
        var text   = label.text();

        alerts.html('');
        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        var data = $(form).find(':input').filter(function () {
            return !$(this).closest('[hidden]').length;
        }).serializeArray();
        data.push({ldelim}name: 'mod-action', value: 'createLei'{rdelim});
        data.push({ldelim}name: 'token', value: '{$csrfToken}'{rdelim});

        function fail(message) {
            alerts.html($('<div class="alert alert-danger" role="alert"></div>').html(message));
            btn.prop('disabled', false);
            label.text(text);
            window.scrollTo(0, alerts.offset().top - 90);
        }

        btn.prop('disabled', true);
        label.text('{$text->t('smimeOrdering')|escape:'javascript'}');
        tssggsLeiPost(data, function (payload) {
            alerts.html($('<div class="alert alert-success" role="alert"></div>').text(payload.message || '{$text->t('leiOrderedSuccessfully')|escape:'javascript'}'));
            window.setTimeout(function () {
                window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
            }, 1200);
        }, fail);
    });
</script>
{/if}
