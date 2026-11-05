<div class="validate-ssl-unified">

    {* $success is shown by main.tpl *}
    {if $errors}
        <div class="alert alert-danger" role="alert">{$errors|escape}</div>
    {/if}
    {if $issued_message}
        <div class="alert alert-info tssggs-issued-message" role="alert">{$issued_message}</div>
    {/if}

    {if $sendEmail}
        <div class="alert alert-success" role="alert">
            {$MGLANG->T('validation_page', 'send_email')}
        </div>
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

    <div class="table-unified-information" style="border-color: #32a353;">
        <p class="desc" style="background: #beecdc;color: #32a353;font-weight: 600;">{$product_title}</p>
        <div class="row">
            <div class="col-sm-6 col-xs-12">
                <div class="row-unified">
                    <h4>{$product_name}</h4>
                    <table>
                        <tbody>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</td>
                            <td>{$api_order_id}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'VendorOrderID')}</td>
                            <td>{$vendor_order_id}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'DomainName')}</td>
                            <td>{$domain}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'Status')}</td>
                            <td class="green-bold">
                                {if $status == 'active'}<i class="fa fa-check" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Issued')}{/if}
                            </td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'ValidFrom')}</td>
                            <td>{if $valid_from}{$valid_from}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'ValidTill')}</td>
                            <td>{if $valid_till}{$valid_till}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'SubscribtionStarts')}</td>
                            <td>{if $subscribtion_starts}{$subscribtion_starts}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'SubscribtionsEnds')}</td>
                            <td>{if $subscribtion_ends}{$subscribtion_ends}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'NextReissue')}</td>
                            <td>{if $next_reissue}{$next_reissue}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-sm-6 col-xs-12">
                <div class="row-unified" style="border-left:2px solid #32a353;">
                    <h4>{$MGLANG->T('validation_page', 'OrderDetails')}</h4>
                    <table>
                        <tbody>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'RegistrationDate')}</td>
                            <td>{$order_details.registration_date}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'OrderExpires')}</td>
                            <td>{$order_details.expiration_date}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'ReccuringAmount')}</td>
                            <td>{$order_details.reccuring_amount}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'BillingCycle')}</td>
                            <td>{$order_details.billing_cycle}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('validation_page', 'PaymentMethod')}</td>
                            <td>{$order_details.payment_method}</td>
                        </tr>
                        </tbody>
                    </table>
                    {if !$certificateUsable}
                    <div class="alert alert-warning" style="margin-top:30px;">{$MGLANG->T('validation_page', 'Certificate not available')}</div>
                    {else}
                    <h4 style="margin-top:30px;">{$MGLANG->T('validation_page', 'Actions')}</h4>
                    <button style="margin-bottom: 5px;" data-toggle="modal" data-target="#myModalCancel" type="button" class="btn btn-default">{$MGLANG->T('validation_page', 'CancelCertificate')}</button>


                    <form method="post" action="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}" style="display:inline-block;margin:0 0 5px;"><input type="hidden" name="mg-action" value="sendCertificate"><input type="hidden" name="token" value="{$csrfToken}"><button type="submit" class="btn btn-default">{$MGLANG->T('validation_page', 'Send Certificate')}</button></form>
                    <button style="margin-bottom: 5px;" data-toggle="modal" data-target="#tssggsViewCertificate" type="button" class="btn btn-default">{$MGLANG->T('validation_page', 'View Certificate')}</button>
                    <div class="btn-group" style="margin-bottom: 5px;">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$MGLANG->T('validation_page', 'Download')} <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            {foreach $download_formats as $format}
                                <li><a class="dropdown-item" href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=download&format={$format}">{$MGLANG->T('download_formats', $format)}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                    {if $visibleRenewButton}<button data-toggle="modal" data-target="#myModalRenew" type="button" style="margin-bottom: 5px;" class="btn btn-success">{$MGLANG->T('validation_page', 'Renew SSL')}</button>{/if}
                    <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=reissueSSL" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Reissue SSL')}</a>
                    {if $install_available}
                        <button style="margin-bottom: 5px;" data-toggle="modal" data-target="#tssggsInstallCertificate" type="button" class="btn btn-primary">{$MGLANG->T('validation_page', 'Install on server')}</button>
                    {/if}
                    {foreach $cpanel_services as $cpanelService}
                        <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$cpanelService.id}&dosinglesignon=1" target="_blank" rel="noopener" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Login to cPanel')}{if $cpanel_services|@count > 1} ({$cpanelService.domain|escape}){/if}</a>
                    {/foreach}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>


{if $configurableOptions.sans || $configurableOptions.sans_wildcard}

    <table style="margin-top: 30px;" class="table-validation-details active-table">
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


{if $organization}

    <table style="margin-top: 30px;" class="table-validation-details active-table">
        <tbody>
        <tr class="title-table">
            <td colspan="2">{$MGLANG->T('validation_page', 'Organization Details')}</td>
        </tr>
        <tr><td>{$MGLANG->T('validation_page', 'Name')}</td><td>{$organization.name|escape}</td></tr>
        {if $organization.duns}<tr><td>{$MGLANG->T('validation_page', 'DUNS Number')}</td><td>{$organization.duns|escape}</td></tr>{/if}
        {if $organization.division}<tr><td>{$MGLANG->T('validation_page', 'Division')}</td><td>{$organization.division|escape}</td></tr>{/if}
        <tr><td>{$MGLANG->T('validation_page', 'Country')}</td><td>{$organization.country|escape}</td></tr>
        <tr><td>{$MGLANG->T('validation_page', 'City')}</td><td>{$organization.city|escape}</td></tr>
        <tr><td>{$MGLANG->T('validation_page', 'Street Address')}</td><td>{$organization.address_line_1|escape}{if $organization.address_line_2}, {$organization.address_line_2|escape}{/if}</td></tr>
        <tr><td>{$MGLANG->T('validation_page', 'Postal Code')}</td><td>{$organization.postal_code|escape}</td></tr>
        </tbody>
    </table>

{/if}

<div style="margin-top: 30px;text-align: left;" class="row">
    <div class="col-md-6 col-sm-12">
        <table class="table-validation-details active-table">
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
        <table class="table-validation-details active-table">
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

<div class="modal fade" id="tssggsViewCertificate" tabindex="-1" role="dialog" aria-labelledby="tssggsViewCertificateLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="text-align: left;" id="tssggsViewCertificateLabel">{$MGLANG->T('validation_page', 'View Certificate')}</h4>
            </div>
            <div class="modal-body" style="text-align: left;">
                <label for="tssggsCrtCode">{$MGLANG->T('validation_page', 'Certificate (CRT)')}</label>
                <textarea id="tssggsCrtCode" class="form-control" rows="10" readonly style="font-family:monospace;font-size:12px;">{$crt_code|escape}</textarea>
                {if $ca_code}
                    <label for="tssggsCaCode" style="margin-top:12px;">{$MGLANG->T('validation_page', 'Intermediate certificates (CA bundle)')}</label>
                    <textarea id="tssggsCaCode" class="form-control" rows="10" readonly style="font-family:monospace;font-size:12px;">{$ca_code|escape}</textarea>
                {/if}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
            </div>
        </div>
    </div>
</div>

{if $install_available}
<div class="modal fade" id="tssggsInstallCertificate" tabindex="-1" role="dialog" aria-labelledby="tssggsInstallCertificateLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}">
                <input type="hidden" name="mg-action" value="installCertificate">
                <input type="hidden" name="token" value="{$csrfToken}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="tssggsInstallCertificateLabel">{$MGLANG->T('validation_page', 'Install on server')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <p>{$MGLANG->T('validation_page', 'install_desc')}</p>
                    <div class="form-group">
                        <label for="tssggsInstallDomain">{$MGLANG->T('validation_page', 'Domain')}</label>
                        <input id="tssggsInstallDomain" class="form-control" name="install_domain" value="{$install_domain|escape}" required>
                    </div>
                    {if !$install_key_stored}
                        <div class="form-group">
                            <label for="tssggsInstallKey">{$MGLANG->T('validation_page', 'Private key')}</label>
                            <textarea id="tssggsInstallKey" class="form-control" name="private_key" rows="8" required style="font-family:monospace;font-size:12px;" placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                            <small class="text-muted">{$MGLANG->T('validation_page', 'install_key_hint')}</small>
                        </div>
                    {/if}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
                    <button type="submit" class="btn btn-primary">{$MGLANG->T('validation_page', 'Install')}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{/if}

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

<div class="modal fade" id="myModalRenew" tabindex="-1" role="dialog" aria-labelledby="myModalRenewLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="mg-action" value="renew">
                <input type="hidden" name="token" value="{$csrfToken}">
                <input type="hidden" name="remoteid" value="{$api_order_id}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="myModalRenewLabel">{$MGLANG->T('validation_page', 'Renew Certificate')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    {$MGLANG->T('validation_page', 'Are you sure you want to renew the certificate?')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
                    <button type="submit" class="btn btn-success">{$MGLANG->T('validation_page', 'Renew')}</button>
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
    });

</script>
