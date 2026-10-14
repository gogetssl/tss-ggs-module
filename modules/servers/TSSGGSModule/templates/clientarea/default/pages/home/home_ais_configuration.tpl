<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
<div class="row">
    <div class="col-sm-12">
        <h3>{$MGLANG->T('aisConfigurationTitle')}</h3>

        <div id="ais-alerts"></div>

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <label for="ais-domain">{$MGLANG->T('aisDomainLabel')}</label>
                    <input type="text"
                           id="ais-domain"
                           class="form-control"
                           placeholder="example.com"
                           value="{$serviceDomain|escape:'htmlall'}">
                    <span class="help-block">{$MGLANG->T('aisDomainHint')}</span>
                </div>

                <button id="ais-submit" class="btn btn-primary">
                    {$MGLANG->T('aisSubmitConfiguration')}
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var serviceUrl = 'clientarea.php?action=productdetails&id={$serviceid}&json=1';

        $('#ais-submit').on('click', function () {
            var domain = $('#ais-domain').val().trim();
            var btn    = $(this);

            $('#ais-alerts').html('');

            if (!domain) {
                $('#MGAlerts').alerts('error', '{$MGLANG->T('aisDomainRequired')}');
                return;
            }

            btn.prop('disabled', true).text('...');

            $.post(serviceUrl, {'mg-action': 'createAis', domain: domain}, function (ret) {
                ret = ret.replace('<JSONRESPONSE#', '').replace('#ENDJSONRESPONSE>', '');
                var payload = JSON.parse(ret);
                if (payload.result === 'error') {
                    $('#MGAlerts').alerts('error', payload.error || 'An error occurred.');
                    btn.prop('disabled', false).text('{$MGLANG->T('aisSubmitConfiguration')}');
                } else {
                    var msg = (payload.data && payload.data.message) ? payload.data.message : '{$MGLANG->T('aisCreatedSuccessfully')}';
                    $('#MGAlerts').alerts('success', msg);
                    window.setTimeout(function () {
                        window.location.href = 'clientarea.php?action=productdetails&id={$serviceid}';
                    }, 1200);
                }
            });
        });
    });
</script>
{/if}
