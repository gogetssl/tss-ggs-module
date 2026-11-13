<div class="validate-ssl-unified tssggs-cert">
    {if $processing_guide}
        <div class="alert alert-info tssggs-processing-guide" role="alert">{$processing_guide}</div>
    {/if}
    {if $autoinstall}
        {assign var=aiDomain value=$autoinstall.domain|escape}
        <div class="alert {if $autoinstall.status == 'installed'}alert-success{elseif $autoinstall.status == 'failed'}alert-danger{elseif $autoinstall.status == 'disabled'}alert-warning{else}alert-info{/if}" role="alert">
            <strong>{$MGLANG->T('autoinstallBoxTitle')}:</strong>
            {if $autoinstall.status == 'installed'}{$MGLANG->T('autoinstallBoxInstalled')|sprintf:$aiDomain}{elseif $autoinstall.status == 'failed'}{$MGLANG->T('autoinstallBoxFailed')|sprintf:$aiDomain}{elseif $autoinstall.status == 'disabled'}{$MGLANG->T('autoinstallBoxDisabled')}{else}{$MGLANG->T('autoinstallBoxAwaiting')|sprintf:$aiDomain}{/if}
            {if $autoinstall.status != 'installed' && $autoinstall.status != 'disabled' && $autoinstall.last_error}<br><small>{$MGLANG->T('autoinstallBoxLastError')} {$autoinstall.last_error|escape}</small>{/if}
            {if $autoinstall.logs}
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;">{$MGLANG->T('autoinstallBoxLogs')}</summary>
                    <ul style="margin:6px 0 0;padding-left:18px;font-size:12px;">
                        {foreach $autoinstall.logs as $aiLog}
                            <li{if $aiLog.type == 'error'} style="color:#c0392b;"{/if}>{$aiLog.date|escape} - {$aiLog.message|escape}</li>
                        {/foreach}
                    </ul>
                </details>
            {/if}
        </div>
    {/if}
    {* ---------- Hero: the certificate authority is validating the order ---------- *}
    <section class="tssggs-card tssggs-hero tssggs-hero--wait">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4M12 16h.01"/></svg>
            </span>
            <div class="tssggs-hero__title">
                <span class="tssggs-badge tssggs-badge--wait">{if $status == 'pending' || $status == 'processing'}{$MGLANG->T('validation_page', 'Validating')}{else}{$status|escape|capitalize}{/if}</span>
                <h3>{$product_name|escape}</h3>
                {if $domain}
                    <p class="tssggs-hero__domain">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
                        {$domain|escape}
                    </p>
                {/if}
            </div>
        </div>
        <div class="tssggs-hero__foot">
            <p>{$product_title}</p>
            <div class="tssggs-actions">
                {if $serviceOpen}
                    <button type="button" data-toggle="modal" data-target="#myModalCancel" class="tssggs-btn">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                        {$MGLANG->T('validation_page', 'CancelCertificate')}
                    </button>
                {/if}
            </div>
            {if !$serviceOpen}
                <p class="tssggs-note tssggs-note--plain">{$MGLANG->T('validation_page', 'Certificate not available')}</p>
            {/if}
        </div>
    </section>

    <div class="tssggs-grid">
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MGLANG->T('validation_page', 'cardCertificate')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('validation_page', 'DomainName')}</dt><dd>{if $domain}{$domain|escape}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidFrom')}</dt><dd>{if $valid_from}{$valid_from}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidTill')}</dt><dd>{if $valid_till}{$valid_till}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'SubscribtionStarts')}</dt><dd>{if $subscribtion_starts}{$subscribtion_starts}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'SubscribtionsEnds')}</dt><dd>{if $subscribtion_ends}{$subscribtion_ends}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'NextReissue')}</dt><dd>{if $next_reissue}{$next_reissue}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$api_order_id}</dd>
                <dt>{$MGLANG->T('validation_page', 'VendorOrderID')}</dt><dd class="tssggs-mono">{$vendor_order_id}</dd>
            </dl>
        </section>

        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                {$MGLANG->T('validation_page', 'cardBilling')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('validation_page', 'RegistrationDate')}</dt><dd>{$order_details.registration_date}</dd>
                <dt>{$MGLANG->T('validation_page', 'OrderExpires')}</dt><dd>{$order_details.expiration_date}</dd>
                <dt>{$MGLANG->T('validation_page', 'ReccuringAmount')}</dt><dd>{$order_details.reccuring_amount}</dd>
                <dt>{$MGLANG->T('validation_page', 'BillingCycle')}</dt><dd>{$order_details.billing_cycle}</dd>
                <dt>{$MGLANG->T('validation_page', 'PaymentMethod')}</dt><dd>{$order_details.payment_method}</dd>
            </dl>
        </section>
    </div>
</div>


<div class="tab-unified-validation-details">

    <h3>{$MGLANG->T('validation_page', 'Order validation')}</h3>
    <p>{$MGLANG->T('validation_page', 'Order validation_desc')}</p>

    {if $previousValidTill}
        <div class="alert alert-success">
            {$previousNotice|escape}
            <div class="btn-group" style="margin-left:8px;">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$MGLANG->T('validation_page', 'Download')} <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    {foreach $download_formats as $format}
                        <li><a class="dropdown-item" href="clientarea.php?action=productdetails&id={$serviceid}&mg-action=download&format={$format}">{$MGLANG->T('download_formats', $format)}</a></li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}

    {if $validationPreparing}
        <div class="alert alert-info" id="validation-preparing">{$MGLANG->T('validation_page', 'Validation details preparing')}</div>
        <script>
            (function () {
                // the gateway adds the file / DNS values a few seconds after the order - reload a few times to show them
                var key = 'validationPreparing{$serviceid}', tries = 0;
                try { tries = parseInt(sessionStorage.getItem(key) || '0', 10); } catch (e) {}
                if (tries < 6) {
                    try { sessionStorage.setItem(key, String(tries + 1)); } catch (e) {}
                    setTimeout(function () { window.location.reload(); }, 10000);
                }
            })();
        </script>
    {else}
        <script>try { sessionStorage.removeItem('validationPreparing{$serviceid}'); } catch (e) {}</script>
    {/if}

    <table class="table-validation-details">
        <tbody>
        <tr class="title-table">
            <td colspan="3">{$MGLANG->T('validation_page', 'Authentification Statuses')}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Authentification Step')}</td>
            <td>{$MGLANG->T('validation_page', 'Authentification Status')}</td>
            <td>{$MGLANG->T('validation_page', 'Last Updated')}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'CSR Status')}</td>
            <td class="green-bold"><i class="fa fa-check" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Completed')}</td>
            <td>{$order_details.csr_status_updated}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'DCV Status')}</td>
            {if $mainDomainApproved}
                <td class="green-bold"><i class="fa fa-check" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Completed')}</td>
            {else}
                <td class="gold-bold"><i class="fa fa-cog" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Pending')}</td>
            {/if}
            <td>-</td>
        </tr>
        {if $validation_product == 'OV' || $validation_product == 'EV'}
            <tr>
                <td>{$MGLANG->T('validation_page', 'Organization Status')}</td>
                <td class="gold-bold"><i class="fa fa-cog" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Pending')}</td>
                <td>-</td>
            </tr>
        {/if}
        <tr>
            <td>{$MGLANG->T('validation_page', 'Vendor Status')}</td>
            <td>{if $vendor_status}{$vendor_status|escape|capitalize}{else}{$MGLANG->T('validation_page', 'awaiting validation (full)')}{/if}</td>
            <td></td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'CN validation method')}</td>
            <td>
                {if $mainDomainMethodLabel}
                    {$mainDomainMethodLabel|escape}
                {elseif $order_details_api.orderData.common_name.validation_method == 'dns' || $order_details_api.orderData.common_name.validation_method == 'http' || $order_details_api.orderData.common_name.validation_method == 'https'}
                    {$order_details_api.orderData.common_name.validation_method|upper}
                {else}
                    {$order_details_api.orderData.common_name.validation_method}
                {/if}
            </td>
            <td></td>
        </tr>

        {if ($mainDomainMethod == 'http' || $mainDomainMethod == 'https') && $mainDomainValidation.file_name}
            <tr>
                <td>{$MGLANG->T('validation_page', 'File Url')}</td>
                <td colspan="2">{$mainDomainMethod}://{$domain}/.well-known/pki-validation/{$mainDomainValidation.file_name}</td>
            </tr>
            <tr>
                <td>{$MGLANG->T('validation_page', 'File Content')}</td>
                <td class="break-all" colspan="2">{$mainDomainValidation.value}</td>
            </tr>
        {/if}

        {if $mainDomainDns}
            {if $mainDomainDns.type}
            <tr>
                <td>{$MGLANG->T('validation_page', 'DNS Record Type')}</td>
                <td colspan="2">{$mainDomainDns.type|escape}</td>
            </tr>
            {/if}
            <tr>
                <td>{$MGLANG->T('validation_page', 'DNS Host')}</td>
                <td class="break-all" colspan="2">{$mainDomainDns.host|escape}</td>
            </tr>
            <tr>
                <td>{$MGLANG->T('validation_page', 'DNS Content')}</td>
                <td class="break-all" colspan="2">{if $mainDomainDns.value}{$mainDomainDns.value|escape}{else}<i>{$MGLANG->T('validation_page', 'DNS value pending')}</i>{/if}</td>
            </tr>
        {/if}

        {* every other domain the gateway validates: SANs and the www name some authorities add *}
        {if $additional_names}

            <tr>
                <td style="vertical-align: top">{$MGLANG->T('validation_page', 'SAN validation')}</td>
                <td colspan="2" style="padding: 0">
                    <table class="table-validation-domains">
                        <tbody>
                        {foreach $additional_names as $san}
                            <tr class="title-domain">
                                <td colspan="2">{$san.name}</td>
                            </tr>
                            <tr>
                                <td style="word-break: break-all;" class="break-all">
                                    {if $san.dns}
                                        {$san.methodLabel|escape}<br>
                                        {if $san.dns.type}{$MGLANG->T('validation_page', 'DNS Record Type')}: {$san.dns.type|escape}<br>{/if}
                                        {$MGLANG->T('validation_page', 'DNS Host')}: {$san.dns.host|escape}<br>
                                        {$MGLANG->T('validation_page', 'DNS Content')}: {if $san.dns.value}{$san.dns.value|escape}{else}<i>{$MGLANG->T('validation_page', 'DNS value pending')}</i>{/if}
                                    {elseif $san.method == 'http' || $san.method == 'https'}
                                        {$san.method|upper}<br>{$san.validation.file_name}<br>{$san.validation.value}
                                    {else}
                                        {$san.method|upper}<br>{$san.validation.value}
                                    {/if}
                                </td>
                                {if $san.approved}
                                    <td class="green-bold"><i class="fa fa-check" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Completed')}</td>
                                {else}
                                    <td class="gold-bold"><i class="fa fa-cog" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Pending')}</td>
                                {/if}
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>

                </td>
            </tr>

        {/if}

        </tbody>
    </table>

    <a href="clientarea.php?action=productdetails&id={$serviceid}&mg-action=downloadCSR" class="btn btn-default">{$MGLANG->T('validation_page', 'Download CSR')}</a>
    {if $serviceOpen}
    {if $mainDomainMethod == 'http' || $mainDomainMethod == 'https'}
        <a href="clientarea.php?action=productdetails&id={$serviceid}&mg-action=downloadAuthFile" class="btn btn-default">{$MGLANG->T('validation_page', 'Download Auth File')}</a>
    {/if}
    <a href="clientarea.php?action=productdetails&id={$serviceid}&mg-action=changeApprovalMethod" class="btn btn-default">{$MGLANG->T('validation_page', 'Change Approval Method')}</a>
    {if $status == 'pending' || $status == 'processing'}
        <form method="POST" style="display:inline;">
            <input type="hidden" name="mg-action" value="revalidate">
            <input type="hidden" name="token" value="{$csrfToken}">
            <button type="submit" class="btn btn-primary" title="{$MGLANG->T('validation_page', 'RevalidateDesc')}">{$MGLANG->T('validation_page', 'Revalidate')}</button>
        </form>
    {/if}
    {if $hasEmailValidation && ($status == 'pending' || $status == 'processing')}
        <form method="POST" style="display:inline;">
            <input type="hidden" name="mg-action" value="resendValidationEmail">
            <input type="hidden" name="token" value="{$csrfToken}">
            <button type="submit" class="btn btn-default" title="{$MGLANG->T('validation_page', 'ResendDcvEmailDesc')}">{$MGLANG->T('validation_page', 'Resend DCV email')}</button>
        </form>
    {/if}
    {/if}

    {if $configurableOptions.sans || $configurableOptions.sans_wildcard}

        <table style="margin-top: 30px;" class="table-validation-details">
            <tbody>
            <tr class="title-table">
                <td colspan="2">{$MGLANG->T('validation_page', 'Additional domains (SAN Items)')}</td>
            </tr>
            {if $configurableOptions.sans}
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Single SAN items')}</td>
                    <td>{$configurableOptions.sans} ({$countSAN} {$MGLANG->T('validation_page', 'used')})</td>
                </tr>
            {/if}
            {if $configurableOptions.sans_wildcard}
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Wildcard SAN items')}</td>
                    <td>{$configurableOptions.sans_wildcard} ({$countSANWildcard} {$MGLANG->T('validation_page', 'used')})</td>
                </tr>
            {/if}
            </tbody>
        </table>

        <a href="{$whmcs_url}/upgrade.php?type=configoptions&id={$serviceid}" style="margin-bottom: 10px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Purchase additional SANs')}</a>

    {/if}


    {if $order_details_api.orderData.contacts.organization}

    <table style="margin-top: 30px;" class="table-validation-details">
        <tbody>
        <tr class="title-table">
            <td colspan="2">{$MGLANG->T('validation_page', 'Organization Details')}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Name')}</td>
            <td>{$order_details_api.orderData.contacts.organization.name}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'DUNS Number')}</td>
            <td>{$order_details_api.orderData.contacts.organization.duns}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Division')}</td>
            <td>{$order_details_api.orderData.contacts.organization.division}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Country')}</td>
            <td>{$order_details_api.orderData.contacts.organization.country}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'City')}</td>
            <td>{$order_details_api.orderData.contacts.organization.city}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Street Address')}</td>
            <td>{$order_details_api.orderData.contacts.organization.address}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Postal Code')}</td>
            <td>{$order_details_api.orderData.contacts.organization.postalcode}</td>
        </tr>
        </tbody>
    </table>

    {/if}

    <div style="margin-top: 30px;" class="row">
        <div class="col-md-6 col-sm-12">
            <table class="table-validation-details">
                <tbody>
                <tr class="title-table">
                    <td colspan="2">{$MGLANG->T('validation_page', 'Admin Details')}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'First Name')}</td>
                    <td>{$order_details_api.orderData.contacts.administrator.first_name}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Last Name')}</td>
                    <td>{$order_details_api.orderData.contacts.administrator.last_name}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Email')}</td>
                    <td>{$order_details_api.orderData.contacts.administrator.email}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Title')}</td>
                    <td>{$order_details_api.orderData.contacts.administrator.title}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Phone')}</td>
                    <td>{$order_details_api.orderData.contacts.administrator.phone}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6 col-sm-12">
            <table class="table-validation-details">
                <tbody>
                <tr class="title-table">
                    <td colspan="2">{$MGLANG->T('validation_page', 'Technical Details')}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'First Name')}</td>
                    <td>{$order_details_api.orderData.contacts.technical.first_name}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Last Name')}</td>
                    <td>{$order_details_api.orderData.contacts.technical.last_name}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Email')}</td>
                    <td>{$order_details_api.orderData.contacts.technical.email}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Title')}</td>
                    <td>{$order_details_api.orderData.contacts.technical.title}</td>
                </tr>
                <tr>
                    <td>{$MGLANG->T('validation_page', 'Phone')}</td>
                    <td>{$order_details_api.orderData.contacts.technical.phone}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>


<div class="modal fade" id="myModalCancel" tabindex="-1" role="dialog" aria-labelledby="myModalCancelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="mg-action" value="cancel">
                <input type="hidden" name="token" value="{$csrfToken}">
                <input type="hidden" name="remoteid" value="{$api_order_id}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="myModalCancelLabel">{$MGLANG->T('validation_page', 'Cancel Certificate')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    {$MGLANG->T('validation_page', 'cancelDialogBody')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'cancelDialogKeep')}</button>
                    <button type="submit" class="btn btn-danger">{$MGLANG->T('validation_page', 'cancelDialogConfirm')}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    $(document).ready(function (){
        let clone_element = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone_element);
        $('.mg-module .validate-ssl-unified').remove();
        // the module's stylesheet lives in the block below and has to survive it
        $('head').append($('link[href*="theme.css"]').first());
        // WHMCS's own tab strip under the page: the module shows everything above it
        $('.product-details').closest('.tab-pane').find('> .nav-tabs, > .tab-content').remove();
    });

</script>
