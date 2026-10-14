<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div id="ais-alerts"></div>

<table id="mainTable" class="table table-bordered">
    <colgroup>
        <col style="width: 25%"/>
        <col style="width: 75%"/>
    </colgroup>
    <tbody>
        <tr>
            <td class="text-left">{$MGLANG->T('aisOrderStatus')}</td>
            <td class="text-left">
                {if $subscription && $subscription->status}
                    {assign var=aisStatus value=$subscription->status|lower}
                {else}
                    {assign var=aisStatus value='pending'}
                {/if}
                {if $aisStatus == 'active'}
                    <span class="label label-success">{$aisStatus|capitalize|escape}</span>
                {elseif $aisStatus == 'pending'}
                    <span class="label label-warning">{$aisStatus|capitalize|escape}</span>
                {else}
                    {$aisStatus|capitalize|escape}
                {/if}
            </td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('aisProductName')}</td>
            <td class="text-left">{$productName|escape}</td>
        </tr>

        {if $subscription}
            {if $subscription->autoinstall_status}
            <tr>
                <td class="text-left">{$MGLANG->T('aisAutoinstallStatus')}</td>
                <td class="text-left">{$subscription->autoinstall_status|capitalize|escape}</td>
            </tr>
            {/if}

            {if $subscription->renewal_date}
            <tr>
                <td class="text-left">{$MGLANG->T('aisRenewalDate')}</td>
                <td class="text-left">{$subscription->renewal_date|escape}</td>
            </tr>
            {/if}

            <tr>
                <td class="text-left">{$MGLANG->T('acmeAutoRenew')}</td>
                <td class="text-left">
                    {if $aisAutoRenew}
                        <span class="label label-success">Enabled</span>
                    {else}
                        <span class="label label-default">Disabled</span>
                    {/if}
                </td>
            </tr>

            {if $subscription->login_sso_link}
            <tr>
                <td class="text-left">{$MGLANG->T('aisLoginLink')}</td>
                <td class="text-left">
                    <a href="{$subscription->login_sso_link|escape:'htmlall'}" target="_blank" class="btn btn-primary btn-sm">
                        {$MGLANG->T('aisLoginLink')}
                    </a>
                </td>
            </tr>
            {/if}

            {if $subscription->manage_sso_link}
            <tr>
                <td class="text-left">{$MGLANG->T('aisManageLink')}</td>
                <td class="text-left">
                    <a href="{$subscription->manage_sso_link|escape:'htmlall'}" target="_blank" class="btn btn-default btn-sm">
                        {$MGLANG->T('aisManageLink')}
                    </a>
                </td>
            </tr>
            {/if}
        {/if}

        <tr>
            <td class="text-left">{$MGLANG->T('Actions')}</td>
            <td class="text-left">
                <button id="ais-refresh" style="margin-bottom: 10px;" class="btn btn-default btn-sm">
                    {$MGLANG->T('aisRefreshDetails')}
                </button>
                {if $subscription && $subscription->api_order_id}
                    {if $aisAutoRenew}
                        <button id="ais-stop-auto-renewal" style="margin-bottom: 10px;" class="btn btn-default btn-sm">
                            {$MGLANG->T('aisStopAutoRenewal')}
                        </button>
                    {/if}
                    <button id="ais-cancel-subscription" style="margin-bottom: 10px;" class="btn btn-danger btn-sm">
                        {$MGLANG->T('aisCancelSubscription')}
                    </button>
                {/if}
            </td>
        </tr>
    </tbody>
</table>

<div class="modal fade" id="aisStopAutoRenewalModal" tabindex="-1" role="dialog" aria-labelledby="aisStopAutoRenewalModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="aisStopAutoRenewalModalLabel">{$MGLANG->T('aisStopAutoRenewal')}</h4>
            </div>
            <div class="modal-body">
                {$MGLANG->T('aisStopAutoRenewalConfirm')}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                <button type="button" class="btn btn-warning" id="aisConfirmStopAutoRenewal">{$MGLANG->T('aisStopAutoRenewal')}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aisCancelSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="aisCancelSubscriptionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="aisCancelSubscriptionModalLabel">{$MGLANG->T('aisCancelSubscription')}</h4>
            </div>
            <div class="modal-body">
                {$MGLANG->T('aisCancelSubscriptionConfirm')}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                <button type="button" class="btn btn-danger" id="aisConfirmCancelSubscription">{$MGLANG->T('aisCancelSubscription')}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';

        function runAction(action, data, options) {
            options = options || {};
            data = data || {};
            data['mg-action'] = action;
            $.post(serviceUrl, data, function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                var payload = JSON.parse(ret);
                if (payload.result === 'error') {
                    var errorMessage = payload.error || 'Error';
                    if (typeof options.onError === 'function') {
                        options.onError(errorMessage, payload);
                    } else {
                        $('#MGAlerts').alerts('error', errorMessage);
                    }
                    return;
                }
                var msg = (payload.data && payload.data.message) ? payload.data.message : 'Done';
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(payload);
                }
                $('#MGAlerts').alerts('success', msg);
                window.setTimeout(function () { window.location.reload(); }, 1200);
            });
        }

        $('body').on('click', '#ais-refresh', function () {
            runAction('refreshAisDetails', {});
        });

        $('body').on('click', '#ais-stop-auto-renewal', function () {
            $('#aisStopAutoRenewalModal').modal('show');
        });

        $('body').on('click', '#ais-cancel-subscription', function () {
            $('#aisCancelSubscriptionModal').modal('show');
        });

        $('body').on('click', '#aisConfirmStopAutoRenewal', function () {
            $('#aisStopAutoRenewalModal').modal('hide');
            runAction('stopAisAutoRenewal', {});
        });

        $('body').on('click', '#aisConfirmCancelSubscription', function () {
            $('#aisCancelSubscriptionModal').modal('hide');
            runAction('cancelAisSubscription', {});
        });
    });
</script>
{/if}
