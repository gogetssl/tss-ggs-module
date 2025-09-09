<div class="validate-ssl-unified">

    {if $sendEmail}
        <div class="alert alert-success" role="alert">
            {$MGLANG->T('validation_page', 'send_email')}
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
                    <h4 style="margin-top:30px;">{$MGLANG->T('validation_page', 'Actions')}</h4>
                    <button style="margin-bottom: 5px;" data-toggle="modal" data-target="#myModalCancel" type="button" class="btn btn-default">{$MGLANG->T('validation_page', 'CancelCertificate')}</button>


                    <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=sendCertificate" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Send Certificate')}</a>
                    <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=downloadCRT" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Download CRT')}</a>
                    <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=downloadICRT" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Download Intermediate CRT')}</a>
                    <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=downloadSSL" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Download SSL')}</a>
                    {if $visibleRenewButton}<button data-toggle="modal" data-target="#myModalRenew" type="button" style="margin-bottom: 5px;" class="btn btn-success">{$MGLANG->T('validation_page', 'Renew SSL')}</button>{/if}
                    <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=reissueSSL" style="margin-bottom: 5px;" class="btn btn-default">{$MGLANG->T('validation_page', 'Reissue SSL')}</a>
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


{if $order_details_api.orderData.contacts.organization}

    <table style="margin-top: 30px;" class="table-validation-details active-table">
        <tbody>
        <tr class="title-table">
            <td colspan="2">{$MGLANG->T('validation_page', 'Organization Details')}</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Name')}</td>
            <td>Test</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'DUNS Number')}</td>
            <td>Name</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Division')}</td>
            <td>test@domain.com</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Country')}</td>
            <td>Poland</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'City')}</td>
            <td>Rzeszów</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Street Address')}</td>
            <td>Rejtana 36</td>
        </tr>
        <tr>
            <td>{$MGLANG->T('validation_page', 'Postal Code')}</td>
            <td>35352</td>
        </tr>
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

<div class="modal fade" id="myModalRenew" tabindex="-1" role="dialog" aria-labelledby="myModalRenewLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="mg-action" value="renew">
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