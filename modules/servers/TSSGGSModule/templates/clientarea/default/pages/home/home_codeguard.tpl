<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
{if $refreshError}
    <div class="alert alert-warning">{$refreshError|escape}</div>
{/if}

<table id="mainTable" class="table table-bordered">
    <colgroup>
        <col style="width: 25%"/>
        <col style="width: 75%"/>
    </colgroup>
    <tbody>
        <tr>
            <td class="text-left">{$MGLANG->T('codeGuardStatus')}</td>
            <td class="text-left">
                {if $codeGuardTone == 'ok'}
                    <span class="label label-success">{$codeGuardStatus|capitalize|escape}</span>
                {elseif $codeGuardTone == 'warn'}
                    <span class="label label-warning">{$codeGuardStatus|capitalize|escape}</span>
                {elseif $codeGuardTone == 'off'}
                    <span class="label label-default">{$codeGuardStatus|capitalize|escape}</span>
                {elseif $codeGuardOrdered}
                    <span class="label label-info">{$codeGuardStatus|capitalize|escape}</span>
                {else}
                    <span class="label label-warning">{$MGLANG->T('codeGuardNotOrdered')}</span>
                {/if}
            </td>
        </tr>
        <tr>
            <td class="text-left">{$MGLANG->T('codeGuardProductName')}</td>
            <td class="text-left">{$productName|escape}</td>
        </tr>

        {if $codeGuardOrdered}
            {if $codeGuardStart && $codeGuardEnd}
            <tr>
                <td class="text-left">{$MGLANG->T('codeGuardPeriod')}</td>
                <td class="text-left">{$codeGuardStart|escape} &ndash; {$codeGuardEnd|escape}</td>
            </tr>
            {/if}

            {if $codeGuardContact}
            <tr>
                <td class="text-left">{$MGLANG->T('codeGuardContact')}</td>
                <td class="text-left">
                    {$codeGuardContact.first_name|escape} {$codeGuardContact.last_name|escape}
                    &lt;{$codeGuardContact.email|escape}&gt;
                </td>
            </tr>
            {/if}

            {foreach from=$codeGuardLicense item=row}
            <tr>
                {* the label comes from the language file, which Lang has escaped already *}
                <td class="text-left">{$row.label}</td>
                <td class="text-left">{$row.value|escape}</td>
            </tr>
            {/foreach}
        {/if}

        <tr>
            <td class="text-left">{$MGLANG->T('Actions')}</td>
            <td class="text-left">
                {if $codeGuardUsable}
                    <button id="codeguard-dashboard" style="margin-bottom: 10px;" class="btn btn-primary btn-sm">
                        {$MGLANG->T('codeGuardOpenDashboard')}
                    </button>
                    <button id="codeguard-refresh" style="margin-bottom: 10px;" class="btn btn-default btn-sm">
                        {$MGLANG->T('codeGuardRefreshDetails')}
                    </button>
                    <button id="codeguard-edit-contact" style="margin-bottom: 10px;" class="btn btn-default btn-sm">
                        {$MGLANG->T('codeGuardChangeContact')}
                    </button>
                {elseif !$codeGuardOrdered}
                    <button id="codeguard-order" style="margin-bottom: 10px;" class="btn btn-primary btn-sm">
                        {$MGLANG->T('codeGuardOrderNow')}
                    </button>
                {/if}
            </td>
        </tr>
    </tbody>
</table>

{if !$codeGuardOrdered}
    <div class="alert alert-info">{$MGLANG->T('codeGuardOrderFailedHint')}</div>
{/if}

<div class="modal fade" id="codeGuardContactModal" tabindex="-1" role="dialog" aria-labelledby="codeGuardContactModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="codeGuardContactModalLabel">{$MGLANG->T('codeGuardChangeContact')}</h4>
            </div>
            <div class="modal-body">
                <p>{$MGLANG->T('codeGuardContactHint')}</p>
                <div class="form-group">
                    <label for="cg_first_name">{$MGLANG->T('codeGuardFirstName')}</label>
                    <input type="text" class="form-control" id="cg_first_name" value="{if $codeGuardContact}{$codeGuardContact.first_name|escape}{/if}">
                </div>
                <div class="form-group">
                    <label for="cg_last_name">{$MGLANG->T('codeGuardLastName')}</label>
                    <input type="text" class="form-control" id="cg_last_name" value="{if $codeGuardContact}{$codeGuardContact.last_name|escape}{/if}">
                </div>
                <div class="form-group">
                    <label for="cg_email">{$MGLANG->T('codeGuardEmail')}</label>
                    <input type="email" class="form-control" id="cg_email" value="{if $codeGuardContact}{$codeGuardContact.email|escape}{/if}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('Close')}</button>
                <button type="button" class="btn btn-primary" id="codeGuardConfirmContact">{$MGLANG->T('codeGuardSaveContact')}</button>
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
            data['token'] = '{$csrfToken}';
            $.post(serviceUrl, data, function (ret) {
                // the JSON answer comes with an application/json header, so jQuery may already have parsed it
                var payload = typeof ret === 'object' ? ret : JSON.parse(String(ret).replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', ''));
                if (payload.result === 'error') {
                    $('#MGAlerts').alerts('error', payload.error || 'Error');
                    return;
                }
                var msg = (payload.data && payload.data.message) ? payload.data.message : 'Done';
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(payload);
                    return;
                }
                $('#MGAlerts').alerts('success', msg);
                window.setTimeout(function () { window.location.reload(); }, 1200);
            });
        }

        $('body').on('click', '#codeguard-dashboard', function () {
            var button = $(this);
            button.prop('disabled', true);
            // the link is one-time and is fetched on every click, so it is opened as soon as it arrives
            runAction('openCodeGuardDashboard', {}, {
                onSuccess: function (payload) {
                    button.prop('disabled', false);
                    var link = payload.data ? payload.data.link : '';
                    if (link) {
                        window.open(link, '_blank', 'noopener');
                    } else {
                        $('#MGAlerts').alerts('error', 'No dashboard link was returned');
                    }
                }
            });
        });

        $('body').on('click', '#codeguard-refresh', function () {
            runAction('refreshCodeGuardDetails', {});
        });

        $('body').on('click', '#codeguard-order', function () {
            runAction('createCodeGuard', {});
        });

        $('body').on('click', '#codeguard-edit-contact', function () {
            $('#codeGuardContactModal').modal('show');
        });

        $('body').on('click', '#codeGuardConfirmContact', function () {
            $('#codeGuardContactModal').modal('hide');
            runAction('updateCodeGuardContact', {
                cg_first_name: $('#cg_first_name').val(),
                cg_last_name: $('#cg_last_name').val(),
                cg_email: $('#cg_email').val()
            });
        });
    });
</script>
{/if}
