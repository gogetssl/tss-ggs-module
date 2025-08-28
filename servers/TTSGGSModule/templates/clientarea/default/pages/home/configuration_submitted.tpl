<div class="validate-ssl-unified">
    <div class="table-unified-information">
        <p class="desc">{$product_title}</p>
        <div class="row">
            <div class="col-sm-6 col-xs-12">
                <div class="row-unified" style="border-right:2px solid #498295">
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
                            <td class="blue-bold">{if $status == 'pending'}<i class="fa fa-cog" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Validating')}{/if}</td>
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
                <div class="row-unified">
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
                    <h4 style="margin-top:30px;">{$MGLANG->T('validation_page', 'Actions')}</h4>
                    <button type="button" data-toggle="modal" data-target="#myModalCancel" class="btn btn-default">{$MGLANG->T('validation_page', 'CancelCertificate')}</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="tab-unified-validation-details">

    <h3>{$MGLANG->T('validation_page', 'Order validation')}</h3>
    <p>{$MGLANG->T('validation_page', 'Order validation_desc')}</p>


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
            <td class="gold-bold"><i class="fa fa-cog" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Pending')}</td>
            <td>-</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Organization Status')}</td>
            <td class="gold-bold"><i class="fa fa-cog" aria-hidden="true"></i> {$MGLANG->T('validation_page', 'Pending')}</td>
            <td>-</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Vendor Status')}</td>
            <td>{$MGLANG->T('validation_page', 'awaiting validation (full)')}</td>
            <td></td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'CN validation method')}</td>
            <td>
                {if $order_details_api.orderData.common_name.validation_method == 'dns' || $order_details_api.orderData.common_name.validation_method == 'http' || $order_details_api.orderData.common_name.validation_method == 'https'}
                    {$order_details_api.orderData.common_name.validation_method|upper}
                {else}
                    {$order_details_api.orderData.common_name.validation_method}
                {/if}
            </td>
            <td></td>
        </tr>

        {if $order_details_api.orderData.common_name.validation_method == 'http' || $order_details_api.orderData.common_name.validation_method == 'https'}
            <tr>
                <td>{$MGLANG->T('validation_page', 'File Url')}</td>
                <td colspan="2">https://{$domain}/.well-known/pki-validation/{$mainDomainValidation.file_name}</td>
            </tr>
            <tr>
                <td>{$MGLANG->T('validation_page', 'File Content')}</td>
                <td class="break-all" colspan="2">{$mainDomainValidation.value}</td>
            </tr>
        {/if}

        {if $order_details_api.orderData.common_name.validation_method == 'dns'}
            <tr>
                <td>{$MGLANG->T('validation_page', 'DNS Content')}</td>
                <td class="break-all" colspan="2">{$mainDomainValidation.value}</td>
            </tr>
        {/if}

        {if $order_details_api.orderData.alternative_names}

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
                                    {if $san.method == 'dns'}
                                        {$san.method|upper}<br>{$san.validation.value}
                                    {elseif $san.method == 'http' || $san.method == 'https'}
                                        {$san.method|upper}<br>{$san.validation.file_name}<br>{$san.validation.value}
                                    {else}
                                        {$san.method|upper}<br>{$san.validation.value}
                                    {/if}
                                </td>
                                <td class="gold-bold"><i class="fa fa-cog" aria-hidden="true"></i> Pending</td>
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
    {if $mainDomainMethod == 'http' || $mainDomainMethod == 'https'}
        <a href="clientarea.php?action=productdetails&id={$serviceid}&mg-action=downloadAuthFile" class="btn btn-default">{$MGLANG->T('validation_page', 'Download Auth File')}</a>
    {/if}
    <a href="clientarea.php?action=productdetails&id={$serviceid}&mg-action=changeApprovalMethod" class="btn btn-default">{$MGLANG->T('validation_page', 'Change Approval Method')}</a>

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
                <input type="hidden" name="remoteid" value="{$api_order_id}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="myModalCancelLabel">{$MGLANG->T('validation_page', 'Cancel Certificate')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    {$MGLANG->T('validation_page', 'Are you sure you want to cancel the certificate?')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
                    <button type="submit" class="btn btn-primary">{$MGLANG->T('validation_page', 'Cancel')}</button>
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