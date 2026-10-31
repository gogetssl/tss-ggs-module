<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<style>
#ssl-config-wrap { max-width: 700px; margin: 0 auto; text-align: left; }
#ssl-config-wrap h2 { font-size: 22px; font-weight: 400; text-align: center; margin-bottom: 20px; }
#ssl-config-wrap .ssl-install-toggle { text-align: center; margin-bottom: 25px; }
#ssl-config-wrap .ssl-install-toggle .btn { min-width: 160px; }
#ssl-config-wrap .ssl-section-title { font-size: 18px; font-weight: 400; margin: 0 0 5px; }
#ssl-config-wrap .ssl-section-sub { font-size: 13px; color: #c0392b; margin-bottom: 15px; }
#ssl-config-wrap .ssl-section-sub.muted { color: #555; }
#ssl-config-wrap .form-group label { font-weight: 700; margin-bottom: 4px; display: block; }
#ssl-config-wrap .form-group label.normal { font-weight: 400; }
#ssl-config-wrap .san-row { display: flex; align-items: center; margin-bottom: 6px; }
#ssl-config-wrap .san-row select { flex: 1; min-width: 0; }
#ssl-config-wrap .san-row .san-btns { margin-left: 6px; white-space: nowrap; }
#ssl-config-wrap .san-row .san-btns .btn { border-radius: 50%; width: 28px; height: 28px; padding: 0; line-height: 26px; font-size: 18px; }
#ssl-config-wrap .san-note { font-style: italic; font-size: 12px; color: #555; margin: 4px 0 10px; }
#ssl-config-wrap .dcv-info { color: #c0392b; font-weight: 700; font-size: 13px; margin-top: 5px; }
#ssl-config-wrap .dcv-note { font-size: 12px; color: #555; margin-top: 5px; }
#ssl-config-wrap .ssl-csr-section,
#ssl-config-wrap .ssl-contact-section { margin-top: 25px; border-top: 1px solid #e0e0e0; padding-top: 20px; }
#ssl-config-wrap .ssl-csr-section h3,
#ssl-config-wrap .ssl-contact-section h3 { font-size: 18px; font-weight: 400; margin-bottom: 4px; }
#ssl-config-wrap .ssl-csr-section .csr-sub { font-size: 13px; color: #555; margin-bottom: 15px; }
#ssl-config-wrap select optgroup { font-weight: 700; font-style: normal; }
#ssl-config-wrap select optgroup option { font-weight: 400; padding-left: 12px; }
#ssl-config-wrap #manual_own_csr { font-family: monospace; font-size: 12px; resize: vertical; }
#ssl-result-wrap { max-width: 700px; margin: 0 auto; text-align: left; }
#ssl-result-wrap #ssl-private-key { font-family: monospace; font-size: 12px; }
</style>

<div id="ssl-config-wrap">
    <h2>{$MGLANG->T('sslConfigGenerateCertTitle')} {$productName|escape}</h2>

    {* Install type toggle - only when the client has a reachable cPanel account *}
    {if $hasCpanel}
    <div class="ssl-install-toggle">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success" id="btn-autoinstall">
                <span class="glyphicon glyphicon-ok"></span> {$MGLANG->T('sslConfigAutoinstall')}
            </button>
            <button type="button" class="btn btn-default" id="btn-manual">
                {$MGLANG->T('sslConfigManual')}
            </button>
        </div>
    </div>
    {/if}
    <input type="hidden" id="install_type" value="{if $hasCpanel}autoinstall{else}manual{/if}">

    {* ===== AUTOINSTALL SECTION ===== *}
    {if $hasCpanel}
    <div id="section-autoinstall">
        <h3 class="ssl-section-title">{$MGLANG->T('sslConfigServerInfoTitle')}</h3>
        <p class="ssl-section-sub">{$MGLANG->T('sslConfigServerInfoText')}</p>

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigDomainLabel')} <span class="text-danger">*</span></label>
            <select class="form-control" id="main_domain">
                <option value="">{$MGLANG->T('sslConfigPleaseChoose')}</option>
                {foreach $domainGroups as $group}
                    <optgroup label="{$group.label|escape}">
                        {foreach $group.domains as $domain}
                            <option value="{$domain|escape}">{$domain|escape}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
                <option value="__manual__">{$MGLANG->T('sslConfigDomainNotListed')}</option>
            </select>
        </div>

        {if $isWildcard}
        <div class="form-group">
            <label class="normal">
                <input type="checkbox" id="wildcard_selected_autoinstall" value="1">
                {$MGLANG->T('sslConfigWildcardLabel')} <span id="wildcard_domain_preview_autoinstall" style="color:#2980b9;font-weight:600;"></span>
            </label>
        </div>
        {else}
        <div class="form-group">
            <label class="normal">
                <input type="checkbox" id="include_www" value="1">
                {$MGLANG->T('sslConfigIncludeWww')}
            </label>
        </div>
        {/if}

        {if $sansLimit > 0}
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigSanSection')} (max {$sansLimit|escape})</label>
            <div id="san-rows-container"></div>
            <p class="san-note">{$MGLANG->T('sslConfigSanNote')}</p>
        </div>
        {/if}

        {if $wildcardSansLimit > 0}
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigSanWildcard')} (max {$wildcardSansLimit|escape})</label>
            <p class="san-note" style="margin-bottom:8px;">{$MGLANG->T('sslConfigSanWildcardAutoNote')}</p>
            <div id="wildcard-san-rows-container"></div>
        </div>
        {/if}

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigValidationMethod')} <span class="text-danger">*</span></label>
            <select class="form-control dcv-select" id="dcv_method_autoinstall">
                {foreach $dcvMethods as $method}
                    <option value="{$method|escape}" {if $method == 'http'}selected{/if}>
                        {if $method == 'email'}{$MGLANG->T('sslConfigValidationEmail')}
                        {elseif $method == 'http'}{$MGLANG->T('sslConfigValidationHttp')}
                        {elseif $method == 'https'}{$MGLANG->T('sslConfigValidationHttps')}
                        {elseif $method == 'dns'}{$MGLANG->T('sslConfigValidationDns')}
                        {else}{$method|escape}{/if}
                    </option>
                {/foreach}
            </select>
            <p class="dcv-note" id="dcv-note-autoinstall-http" style="display:none;">{$MGLANG->T('sslConfigHttpAutoNote')}</p>
            <p class="dcv-note" id="dcv-note-autoinstall-dns" style="display:none;">{$MGLANG->T('sslConfigDnsNote')}</p>
        </div>

        <div id="approver-email-autoinstall-section" style="display:none;">
            <div id="approver-email-autoinstall-warning" style="display:none;" class="alert alert-warning">
                {$MGLANG->T('sslConfigEmailDcvNeedsDomain')}
            </div>
            <div id="approver-email-autoinstall-select-wrap" class="form-group" style="display:none;">
                <label>{$MGLANG->T('sslConfigDcvEmailLabel')} <span class="text-danger">*</span></label>
                <select class="form-control" id="approver_email_autoinstall">
                    <option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>
                </select>
                <p class="dcv-info">{$MGLANG->T('sslConfigDcvEmailFallback')}</p>
            </div>
        </div>

        <div id="dns-fallback-email-section" style="display:none;">
            <div id="dns-fallback-domain-warning" style="display:none;" class="alert alert-info">
                {$MGLANG->T('sslConfigDnsFallbackNeedsDomain')}
            </div>
            <div id="dns-fallback-select-wrap" class="form-group" style="display:none;">
                <label>{$MGLANG->T('sslConfigDnsFallbackLabel')}</label>
                <select class="form-control" id="dns_fallback_email">
                    <option value="">{$MGLANG->T('sslConfigDnsFallbackAutoSelect')}</option>
                </select>
                <p class="dcv-info">{$MGLANG->T('sslConfigDnsFallbackHint')}</p>
            </div>
        </div>
    </div>
    {/if}

    {* ===== MANUAL SECTION ===== *}
    <div id="section-manual" {if $hasCpanel}style="display:none;"{/if}>
        <p class="ssl-section-sub muted">{$MGLANG->T('sslConfigManualInfoText')}</p>

        <div class="form-group" id="manual-own-csr-group">
            <label>{$MGLANG->T('sslConfigOwnCsrLabel')} <span class="text-danger">*</span></label>
            <textarea class="form-control" id="manual_own_csr" rows="8"
                placeholder="-----BEGIN CERTIFICATE REQUEST-----&#10;...&#10;-----END CERTIFICATE REQUEST-----"></textarea>
            <span class="help-block" style="font-size:12px;color:#888;">{$MGLANG->T('sslConfigOwnCsrHint')}</span>
        </div>

        {if $enableCsrGenerator}
        <div class="form-group">
            <label class="normal">
                <input type="checkbox" id="manual_auto_generate_csr" style="margin-right:6px;">
                {$MGLANG->T('sslConfigAutoGenerateCsr')}
            </label>
        </div>
        <div class="form-group" id="manual-domain-group" style="display:none;">
            <label>{$MGLANG->T('sslConfigCsrCommonName')} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="manual_domain" placeholder="{$productDomain|escape}" value="{$productDomain|escape}">
            <span class="help-block" style="font-size:12px;color:#888;">{$MGLANG->T('sslConfigCsrCommonNameHint')}</span>
        </div>

        {if $isWildcard}
        <div class="form-group" id="manual-wildcard-group" style="display:none;">
            <label class="normal">
                <input type="checkbox" id="wildcard_selected_manual" value="1">
                {$MGLANG->T('sslConfigWildcardLabel')} <span id="wildcard_domain_preview_manual" style="color:#2980b9;font-weight:600;"></span>
            </label>
        </div>
        {/if}
        {/if}

        {if $sansLimit > 0}
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigSanSingle')} (max {$sansLimit|escape})</label>
            <textarea class="form-control" id="san_domains_manual" rows="4" placeholder="domain1.com&#10;domain2.com"></textarea>
        </div>
        {/if}
        {if $wildcardSansLimit > 0}
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigSanWildcard')} (max {$wildcardSansLimit|escape})</label>
            <textarea class="form-control" id="wildcard_san_manual" rows="4" placeholder="*.domain1.com&#10;*.domain2.com"></textarea>
        </div>
        {/if}

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigValidationMethod')} <span class="text-danger">*</span></label>
            <select class="form-control dcv-select" id="dcv_method_manual">
                {foreach $dcvMethods as $method}
                    <option value="{$method|escape}" {if $method == 'http'}selected{/if}>
                        {if $method == 'email'}{$MGLANG->T('sslConfigValidationEmail')}
                        {elseif $method == 'http'}{$MGLANG->T('sslConfigValidationHttp')}
                        {elseif $method == 'https'}{$MGLANG->T('sslConfigValidationHttps')}
                        {elseif $method == 'dns'}{$MGLANG->T('sslConfigValidationDns')}
                        {else}{$method|escape}{/if}
                    </option>
                {/foreach}
            </select>
        </div>

        <div id="approver-email-manual-section" style="display:none;">
            <div id="approver-email-manual-warning" style="display:none;" class="alert alert-warning"></div>
            <div id="approver-email-manual-select-wrap" class="form-group" style="display:none;">
                <label>{$MGLANG->T('sslConfigDcvEmailLabel')} <span class="text-danger">*</span></label>
                <select class="form-control" id="approver_email_manual">
                    <option value="">{$MGLANG->T('sslConfigFirstSelectDomain')}</option>
                </select>
                <p class="dcv-info">{$MGLANG->T('sslConfigDcvEmailFallback')}</p>
            </div>
        </div>
    </div>

    {* ===== CSR DETAILS (only when the CSR is generated by the module) ===== *}
    <div class="ssl-csr-section" id="ssl-csr-section" {if !$hasCpanel}style="display:none;"{/if}>
        <h3>{$MGLANG->T('sslConfigCsrSection')}</h3>
        <p class="csr-sub">{$MGLANG->T('sslConfigCsrSubtext')}</p>

        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrCountry')} <span class="text-danger">*</span></label>
            <select class="form-control" id="csr_country">
                {foreach $countries as $code => $name}
                    <option value="{$code|escape}" {if $code == $csrDefaults.country}selected{/if}>{$name|escape}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrState')} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="csr_state" value="{$csrDefaults.state|escape}">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrCity')} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="csr_city" value="{$csrDefaults.city|escape}">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrOrg')}</label>
            <input type="text" class="form-control" id="csr_org" value="{$csrDefaults.org|escape}">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrOrgUnit')}</label>
            <input type="text" class="form-control" id="csr_org_unit" value="">
        </div>
        <div class="form-group">
            <label>{$MGLANG->T('sslConfigCsrEmail')}</label>
            <input type="email" class="form-control" id="csr_email" value="{$csrDefaults.email|escape}">
        </div>
    </div>

    {* ===== ADMIN CONTACT ===== *}
    {if !$adminFormHidden}
    <div class="ssl-contact-section">
        <h3 style="margin-bottom:15px;">{$MGLANG->T('sslConfigAdminContact')}</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigFirstname')} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="admin_firstname" value="{if $client}{$client->firstname|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigLastname')} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="admin_lastname" value="{if $client}{$client->lastname|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigEmail')} <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="admin_email" value="{if $client}{$client->email|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigPhone')} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="admin_phone" value="{if $client}{$client->phonenumber|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigTitle')}</label>
                    <input type="text" class="form-control" id="admin_title" value="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigAddress')}</label>
                    <input type="text" class="form-control" id="admin_addressline1" value="{if $client}{$client->address1|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigCity')}</label>
                    <input type="text" class="form-control" id="admin_city" value="{if $client}{$client->city|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigRegion')}</label>
                    <input type="text" class="form-control" id="admin_region" value="{if $client}{$client->state|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigPostalcode')}</label>
                    <input type="text" class="form-control" id="admin_postalcode" value="{if $client}{$client->postcode|escape}{/if}">
                </div>
                <div class="form-group">
                    <label>{$MGLANG->T('sslConfigCountry')}</label>
                    <select class="form-control" id="admin_country">
                        {foreach $countries as $code => $name}
                            <option value="{$code|escape}" {if $code == $clientCountry}selected{/if}>{$name|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}

    <div style="margin-top:25px; padding-top:15px; border-top:1px solid #e0e0e0;">
        <button type="button" class="btn btn-primary btn-lg" id="btn-submit-ssl-config">{$MGLANG->T('sslConfigSubmit')}</button>
    </div>
</div>

{* ===== RESULT PANELS ===== *}
<div id="ssl-result-wrap">

    {* AutoInstall submitted *}
    <div id="ssl-thankyou-autoinstall" class="panel panel-default" style="display:none;">
        <div class="panel-heading" style="background:#f0f7ff;border-color:#b3d4f5;">
            <strong>{$MGLANG->T('autoinstallStatusTitle')}</strong>
        </div>
        <div class="panel-body" style="font-size:13px;color:#444;">
            {if $certTypeConfig == 'DV'}
                {if !$isWildcard}<p><strong>{$MGLANG->T('thankYou')}</strong></p>{/if}
                <p>{if $isWildcard}{$MGLANG->T('autoinstallWildcardDvIntro')}{else}{$MGLANG->T('autoinstallSingleDvMsg')}{/if}</p>
            {else}
                <p>{$MGLANG->T('autoinstallOvIntro')}</p>
                <p><strong>{$MGLANG->T('autoinstallOvNextStep')}</strong><br>{$MGLANG->T('autoinstallOvMsg')}</p>
            {/if}
            <p class="ssl-ty-email-row" style="display:none;">{$MGLANG->T('autoinstallEmailMsg')} <strong class="ssl-ty-email"></strong></p>
            {if $certTypeConfig == 'DV'}
                {if $isWildcard}<p>{$MGLANG->T('autoinstallOutroDomain')}</p>{/if}
            {else}
                <p>{$MGLANG->T('autoinstallOutroDomainOrg')}</p>
            {/if}
        </div>
        <div class="panel-footer">
            <a href="clientarea.php?action=productdetails&id={$serviceid}" class="btn btn-primary">{$MGLANG->T('goToCertificateDetails')}</a>
        </div>
    </div>

    {* Manual enrollment with a generated CSR - private key has to be saved by the client *}
    <div id="ssl-thankyou-manual-key" class="panel panel-default" style="display:none;">
        <div class="panel-heading" style="background:#fff8e1;border-color:#f8e7aa;">
            <strong>{$MGLANG->T('manualPrivateKeyTitle')}</strong>
        </div>
        <div class="panel-body" style="font-size:13px;color:#444;">
            <p>{$MGLANG->T('manualPrivateKeyMsg')}</p>
            <textarea class="form-control" id="ssl-private-key" rows="10" readonly></textarea>
        </div>
        <div class="panel-footer">
            <a href="#" id="ssl-private-key-download" class="btn btn-success">{$MGLANG->T('manualPrivateKeyDownload')}</a>
            <a href="clientarea.php?action=productdetails&id={$serviceid}" class="btn btn-primary">{$MGLANG->T('goToCertificateDetails')}</a>
        </div>
    </div>
</div>

{* Hidden templates for SAN rows (autoinstall) *}
<select id="san-domain-template" style="display:none;">
    <option value="">{$MGLANG->T('sslConfigPleaseChoose')}</option>
    {foreach $domainGroups as $group}
        <optgroup label="{$group.label|escape}">
            {foreach $group.domains as $domain}
                <option value="{$domain|escape}">{$domain|escape}</option>
            {/foreach}
        </optgroup>
    {/foreach}
</select>
<select id="wildcard-san-domain-template" style="display:none;">
    <option value="">{$MGLANG->T('sslConfigPleaseChoose')}</option>
    {foreach $domainGroups as $group}
        <optgroup label="{$group.label|escape}">
            {foreach $group.domains as $domain}
                <option value="{$domain|escape}">*.{$domain|escape}</option>
            {/foreach}
        </optgroup>
    {/foreach}
</select>

<script type="text/javascript">
(function ($) {
    var serviceUrl       = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';
    var sanLimit         = {$sansLimit|intval};
    var wildcardSanLimit = {$wildcardSansLimit|intval};
    var hasCpanel        = {if $hasCpanel}true{else}false{/if};
    var hasCsrGenerator  = {if $enableCsrGenerator}true{else}false{/if};
    var lang = {
        loading:          '{$MGLANG->T('sslConfigLoading')|escape:'javascript'}',
        firstSelect:      '{$MGLANG->T('sslConfigFirstSelectDomain')|escape:'javascript'}',
        needsCsr:         '{$MGLANG->T('sslConfigEmailDcvNeedsCsr')|escape:'javascript'}',
        csrNoCn:          '{$MGLANG->T('sslConfigEmailDcvCsrNoCn')|escape:'javascript'}',
        needsCn:          '{$MGLANG->T('sslConfigEmailDcvNeedsCn')|escape:'javascript'}',
        ownCsrRequired:   '{$MGLANG->T('sslConfigOwnCsrRequired')|escape:'javascript'}',
        domainRequired:   '{$MGLANG->T('sslConfigDomainRequired')|escape:'javascript'}',
        approverRequired: '{$MGLANG->T('sslConfigApproverEmailRequired')|escape:'javascript'}',
        error:            '{$MGLANG->T('sslConfigAnErrorOccurred')|escape:'javascript'}',
        dnsFallbackAuto:  '{$MGLANG->T('sslConfigDnsFallbackAutoSelect')|escape:'javascript'}'
    };

    function parseResponse(ret) {
        ret = String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
        return JSON.parse(ret);
    }

    function decodeEntities(text) {
        return $('<textarea>').html(text).text();
    }

    // ---- Install type toggle ----
    function isManualCsrGenerated() {
        return hasCsrGenerator && $('#manual_auto_generate_csr').is(':checked');
    }

    function switchToAutoinstall() {
        $('#install_type').val('autoinstall');
        $('#btn-autoinstall').addClass('btn-success').removeClass('btn-default');
        $('#btn-manual').addClass('btn-default').removeClass('btn-success');
        $('#section-autoinstall').show();
        $('#section-manual').hide();
        $('#ssl-csr-section').show();
    }

    function switchToManual() {
        $('#install_type').val('manual');
        $('#btn-manual').addClass('btn-success').removeClass('btn-default');
        $('#btn-autoinstall').addClass('btn-default').removeClass('btn-success');
        $('#section-autoinstall').hide();
        $('#section-manual').show();
        if ($('#main_domain').val() === '__manual__') {
            $('#main_domain').val('');
        }
        updateManualCsrMode();
    }

    $('#btn-autoinstall').on('click', switchToAutoinstall);
    $('#btn-manual').on('click', switchToManual);

    // ---- Own CSR / generated CSR (manual) ----
    function updateManualCsrMode() {
        if (isManualCsrGenerated()) {
            $('#manual-own-csr-group').hide();
            $('#manual-domain-group, #manual-wildcard-group').show();
            $('#ssl-csr-section').show();
        } else {
            $('#manual-own-csr-group').show();
            $('#manual-domain-group, #manual-wildcard-group').hide();
            $('#ssl-csr-section').hide();
        }
        refreshManualEmailSection();
    }
    $('#manual_auto_generate_csr').on('change', updateManualCsrMode);

    // ---- Approver emails ----
    function loadApproverEmails(domain, $select, $wrap, emptyOptionText) {
        $wrap.show();
        $select.html($('<option>').val('').text(lang.loading));
        $.post(serviceUrl, { 'mg-action': 'getApproverEmails', 'domain': domain }, function (ret) {
            var emails = [];
            try {
                var payload = parseResponse(ret);
                emails = (payload.data && payload.data.emails) ? payload.data.emails : [];
            } catch (e) {}
            $select.empty();
            if (emptyOptionText) {
                $select.append($('<option>').val('').text(decodeEntities(emptyOptionText)));
                $.each(emails, function (i, email) {
                    $select.append($('<option>').val(email).text(email));
                });
            } else if (emails.length) {
                $.each(emails, function (i, email) {
                    $select.append($('<option>').val(email).text(email));
                });
            } else {
                $select.append($('<option>').val('').text(lang.firstSelect));
            }
        });
    }

    function refreshAutoinstallDcv() {
        var method = $('#dcv_method_autoinstall').val();
        $('#dcv-note-autoinstall-http').toggle(method === 'http' || method === 'https');
        $('#dcv-note-autoinstall-dns').toggle(method === 'dns');

        var domain = $('#main_domain').val();

        if (method === 'dns') {
            $('#dns-fallback-email-section').show();
            if (!domain || domain === '__manual__') {
                $('#dns-fallback-domain-warning').show();
                $('#dns-fallback-select-wrap').hide();
            } else {
                $('#dns-fallback-domain-warning').hide();
                loadApproverEmails(domain, $('#dns_fallback_email'), $('#dns-fallback-select-wrap'), lang.dnsFallbackAuto);
            }
        } else {
            $('#dns-fallback-email-section').hide();
        }

        if (method !== 'email') {
            $('#approver-email-autoinstall-section').hide();
            return;
        }
        $('#approver-email-autoinstall-section').show();
        if (!domain || domain === '__manual__') {
            $('#approver-email-autoinstall-warning').show();
            $('#approver-email-autoinstall-select-wrap').hide();
        } else {
            $('#approver-email-autoinstall-warning').hide();
            loadApproverEmails(domain, $('#approver_email_autoinstall'), $('#approver-email-autoinstall-select-wrap'));
        }
    }

    function extractCnFromCsr(pem) {
        try {
            var b64    = pem.replace(/-----[^-]+-----/g, '').replace(/\s+/g, '');
            var binary = atob(b64);
            var bytes  = new Uint8Array(binary.length);
            for (var i = 0; i < binary.length; i++) { bytes[i] = binary.charCodeAt(i); }
            // CN OID: 55 04 03
            for (var j = 0; j < bytes.length - 5; j++) {
                if (bytes[j] === 0x55 && bytes[j + 1] === 0x04 && bytes[j + 2] === 0x03) {
                    var tag = bytes[j + 3];
                    // PrintableString(0x13), UTF8String(0x0c), IA5String(0x16)
                    if (tag === 0x13 || tag === 0x0c || tag === 0x16) {
                        var len = bytes[j + 4];
                        if (len > 0 && len < 128 && j + 5 + len <= bytes.length) {
                            var cn = '';
                            for (var k = 0; k < len; k++) { cn += String.fromCharCode(bytes[j + 5 + k]); }
                            return cn.toLowerCase();
                        }
                    }
                }
            }
        } catch (e) {}
        return null;
    }

    function refreshManualEmailSection() {
        if ($('#dcv_method_manual').val() !== 'email') {
            $('#approver-email-manual-section').hide();
            return;
        }
        $('#approver-email-manual-section').show();

        var cn = null, warning = null;
        if (isManualCsrGenerated()) {
            cn = $.trim($('#manual_domain').val());
            if (!cn) { warning = lang.needsCn; }
        } else {
            var csrText = $.trim($('#manual_own_csr').val());
            if (!csrText) {
                warning = lang.needsCsr;
            } else {
                cn = extractCnFromCsr(csrText);
                if (!cn) { warning = lang.csrNoCn; }
            }
        }

        if (warning) {
            $('#approver-email-manual-warning').text(decodeEntities(warning)).show();
            $('#approver-email-manual-select-wrap').hide();
        } else {
            $('#approver-email-manual-warning').hide();
            loadApproverEmails(cn, $('#approver_email_manual'), $('#approver-email-manual-select-wrap'));
        }
    }

    $('#main_domain').on('change', function () {
        if ($(this).val() === '__manual__') {
            switchToManual();
            return;
        }
        refreshAutoinstallDcv();
        updateWildcardPreview('wildcard_selected_autoinstall', 'main_domain', 'wildcard_domain_preview_autoinstall');
    });
    $('#dcv_method_autoinstall').on('change', refreshAutoinstallDcv);
    $('#dcv_method_manual').on('change', refreshManualEmailSection);
    $('#manual_domain').on('change blur', function () {
        refreshManualEmailSection();
        updateWildcardPreview('wildcard_selected_manual', 'manual_domain', 'wildcard_domain_preview_manual');
    });
    $('#manual_own_csr').on('blur', refreshManualEmailSection);

    // ---- SAN rows (autoinstall) ----
    function addSanRow(containerId, templateId, inputName, limit) {
        var $container = $('#' + containerId);
        if ($container.children('.san-row').length >= limit) return;

        var $row  = $('<div class="san-row">');
        var $sel  = $('<select class="form-control">').attr('name', inputName).html($('#' + templateId).html());
        var $btns = $('<div class="san-btns">');
        var $add  = $('<button type="button" class="btn btn-success san-btn-add">+</button>');
        var $rem  = $('<button type="button" class="btn btn-danger">&minus;</button>');

        $add.on('click', function () { addSanRow(containerId, templateId, inputName, limit); });
        $rem.on('click', function () {
            $row.remove();
            if (!$container.children('.san-row').length) {
                addSanRow(containerId, templateId, inputName, limit);
            }
            updateSanButtons($container, limit);
        });

        $btns.append($add).append(' ').append($rem);
        $row.append($sel).append($btns);
        $container.append($row);
        updateSanButtons($container, limit);
    }

    function updateSanButtons($container, limit) {
        $container.find('.san-btn-add').toggle($container.children('.san-row').length < limit);
    }

    if (hasCpanel && sanLimit > 0) {
        addSanRow('san-rows-container', 'san-domain-template', 'san_domains[]', sanLimit);
    }
    if (hasCpanel && wildcardSanLimit > 0) {
        addSanRow('wildcard-san-rows-container', 'wildcard-san-domain-template', 'wildcard_san_domains[]', wildcardSanLimit);
    }

    // ---- Wildcard preview ----
    function updateWildcardPreview(checkboxId, domainId, previewId) {
        var checked = $('#' + checkboxId).is(':checked');
        var domain  = $('#' + domainId).val();
        if (checked && domain && domain !== '__manual__') {
            $('#' + previewId).text('(*.' + domain.replace(/^\*\./, '') + ')');
        } else {
            $('#' + previewId).text('');
        }
    }
    $('#wildcard_selected_autoinstall').on('change', function () {
        updateWildcardPreview('wildcard_selected_autoinstall', 'main_domain', 'wildcard_domain_preview_autoinstall');
    });
    $('#wildcard_selected_manual').on('change', function () {
        updateWildcardPreview('wildcard_selected_manual', 'manual_domain', 'wildcard_domain_preview_manual');
    });

    // ---- Submit ----
    function showError(message) {
        $('#MGAlerts').alerts('error', message || lang.error);
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    $('#btn-submit-ssl-config').on('click', function () {
        var btn         = $(this);
        var installType = $('#install_type').val();
        var dcvMethod, approverEmail;

        if (installType === 'autoinstall') {
            dcvMethod     = $('#dcv_method_autoinstall').val();
            approverEmail = dcvMethod === 'email' ? ($('#approver_email_autoinstall').val() || '') : '';
        } else {
            dcvMethod     = $('#dcv_method_manual').val();
            approverEmail = dcvMethod === 'email' ? ($('#approver_email_manual').val() || '') : '';
        }
        if (dcvMethod === 'email' && !approverEmail) {
            showError(lang.approverRequired);
            return;
        }

        var postData = {
            'mg-action':          'submitSslConfiguration',
            'token':              '{$csrfToken}',
            'install_type':       installType,
            'dcv_method':         dcvMethod,
            'approver_email':     approverEmail,
            'include_www':        $('#include_www').is(':checked') ? 1 : 0,
            'csr_country':        $('#csr_country').val(),
            'csr_state':          $('#csr_state').val(),
            'csr_city':           $('#csr_city').val(),
            'csr_org':            $('#csr_org').val(),
            'csr_org_unit':       $('#csr_org_unit').val(),
            'csr_email':          $('#csr_email').val(),
            'admin_firstname':    $('#admin_firstname').val(),
            'admin_lastname':     $('#admin_lastname').val(),
            'admin_email':        $('#admin_email').val(),
            'admin_phone':        $('#admin_phone').val(),
            'admin_title':        $('#admin_title').val(),
            'admin_addressline1': $('#admin_addressline1').val(),
            'admin_city':         $('#admin_city').val(),
            'admin_region':       $('#admin_region').val(),
            'admin_postalcode':   $('#admin_postalcode').val(),
            'admin_country':      $('#admin_country').val()
        };

        if (installType === 'autoinstall') {
            var mainDomain = $('#main_domain').val();
            if (!mainDomain || mainDomain === '__manual__') {
                showError(lang.domainRequired);
                return;
            }
            if ($('#wildcard_selected_autoinstall').is(':checked') && mainDomain.indexOf('*.') !== 0) {
                mainDomain = '*.' + mainDomain;
            }
            postData['main_domain'] = mainDomain;

            var sans = [];
            $('select[name="san_domains[]"]').each(function () {
                var v = $(this).val();
                if (v) sans.push(v);
            });
            postData['san_domains'] = sans.join("\n");

            var wildcardSans = [];
            $('select[name="wildcard_san_domains[]"]').each(function () {
                var v = $(this).val();
                if (v) wildcardSans.push(v.indexOf('*.') === 0 ? v : '*.' + v);
            });
            postData['wildcard_san'] = wildcardSans.join("\n");

            if (dcvMethod === 'dns') {
                postData['dns_fallback_email'] = $('#dns_fallback_email').val() || '';
            }
        } else {
            if (isManualCsrGenerated()) {
                var manualDomain = $.trim($('#manual_domain').val());
                if (!manualDomain) {
                    showError(lang.domainRequired);
                    return;
                }
                if ($('#wildcard_selected_manual').is(':checked') && manualDomain.indexOf('*.') !== 0) {
                    manualDomain = '*.' + manualDomain;
                }
                postData['manual_domain'] = manualDomain;
            } else {
                var ownCsr = $.trim($('#manual_own_csr').val());
                if (!ownCsr) {
                    showError(lang.ownCsrRequired);
                    return;
                }
                postData['own_csr'] = ownCsr;
            }
            postData['san_domains']  = $('#san_domains_manual').val() || '';
            postData['wildcard_san'] = $('#wildcard_san_manual').val() || '';
        }

        btn.prop('disabled', true);

        $.post(serviceUrl, postData, function (ret) {
            var payload;
            try {
                payload = parseResponse(ret);
            } catch (e) {
                btn.prop('disabled', false);
                showError(lang.error);
                return;
            }

            if (payload.result !== 'success' || !payload.data) {
                btn.prop('disabled', false);
                showError(payload.error);
                return;
            }

            var data = payload.data;
            if (data.install_type === 'autoinstall') {
                if (data.approver_email) {
                    $('.ssl-ty-email').text(data.approver_email);
                    $('.ssl-ty-email-row').show();
                }
                $('#ssl-config-wrap').hide();
                $('#ssl-thankyou-autoinstall').show();
                $('html, body').animate({ scrollTop: 0 }, 300);
            } else if (data.private_key) {
                $('#ssl-private-key').val(data.private_key);
                $('#ssl-private-key-download').attr('href', data.private_key_url);
                $('#ssl-config-wrap').hide();
                $('#ssl-thankyou-manual-key').show();
                $('html, body').animate({ scrollTop: 0 }, 300);
            } else {
                window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
            }
        }).fail(function () {
            btn.prop('disabled', false);
            showError(lang.error);
        });
    });

    // ---- Init ----
    if (hasCpanel) {
        refreshAutoinstallDcv();
    } else {
        updateManualCsrMode();
    }

}(jQuery));
</script>
{/if}
