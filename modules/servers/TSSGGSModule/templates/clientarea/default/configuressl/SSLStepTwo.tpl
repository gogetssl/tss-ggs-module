{literal}
<script>

    $(document).ready(function (){

        $('body').on('change','.change-dcv-method', function (){

            if($(this).is(':checked'))
            {
                if($(this).val() == 'email')
                {
                    $('.select_method').val('email').trigger("change");
                    $('input.change-dcv-method[value="http"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="https"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="dns"]').prop('checked', false).trigger("change");
                }
                else if($(this).val() == 'http')
                {
                    $('.select_method').val('http').trigger("change");
                    $('input.change-dcv-method[value="email"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="https"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="dns"]').prop('checked', false).trigger("change");
                }
                else if($(this).val() == 'https')
                {
                    $('.select_method').val('https').trigger("change");
                    $('input.change-dcv-method[value="email"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="http"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="dns"]').prop('checked', false).trigger("change");
                }
                else if($(this).val() == 'dns')
                {
                    $('.select_method').val('dns').trigger("change");
                    $('input.change-dcv-method[value="email"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="http"]').prop('checked', false).trigger("change");
                    $('input.change-dcv-method[value="https"]').prop('checked', false).trigger("change");
                }
            }

        });

        $('body').on('change', '.select_method', function (){

            let method = $(this).val();
            if(method == 'email')
            {
                $(this).parents('tr').find('select.select_email').show();
            }
            else
            {
                $(this).parents('tr').find('select.select_email').hide();
            }

        });


        $('#btnOrderContinue').css({'margin-bottom': '30px'});
        $('input[name="approval_method"]').parents('form').parent('.card-body').css({'overflow': 'hidden'});
        $('input[name="approval_method"]').parents('form').find('div.tab-content').html('<div class="step2-validation-certificate"></div>');
        $('input[name="approval_method"]').parents('form').find('label').remove();

        let optionsMethod = '';
        let availableMethod = {/literal}{$available_method_validation}{literal};
        for (let index = 0; index < availableMethod.length; ++index) {
            const element = availableMethod[index];
            optionsMethod += '<option value="'+element+'">'+element.toUpperCase()+'</option>';
        }

        $('.step2-validation-certificate').append('<div class="alert alert-info" role="alert">' +
            '<p>{/literal}{$MGLANG->T('validaion_page', 'description')}{literal}' +
            '<p></p>' +
            '<p><b>{/literal}{$MGLANG->T('validaion_page', 'email')}{literal} </b>{/literal}{$MGLANG->T('validaion_page', 'email_desc')}{literal}</p>' +
            '<p></p>' +
            '<p><b>{/literal}{$MGLANG->T('validaion_page', 'dns')}{literal} </b>{/literal}{$MGLANG->T('validaion_page', 'dns_desc')}{literal}</p>' +
            '<p></p>' +
            '<p><b>{/literal}{$MGLANG->T('validaion_page', 'http')}{literal} </b>{/literal}{$MGLANG->T('validaion_page', 'http_desc')}{literal}</p>' +
            '</div>');

        $('<style>.label-text{position: absolute;width: 200px;left: 70px;top: 5px;cursor: pointer;}.switch{position:relative;display:block;width:60px;height:34px}.switch input{opacity:0;width:0;height:0}.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;-webkit-transition:.4s;transition:.4s}.slider:before{position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;background-color:#fff;-webkit-transition:.4s;transition:.4s}input:checked+.slider{background-color:#369}input:focus+.slider{box-shadow:0 0 1px #2196f3}input:checked+.slider:before{-webkit-transform:translateX(26px);-ms-transform:translateX(26px);transform:translateX(26px)}.slider.round{border-radius:34px}.slider.round:before{border-radius:50%}</style>').appendTo("head");

        $('.step2-validation-certificate').append('<div class="row switch-row" style="margin:20px 0;"></div>');

        if(availableMethod.indexOf('email') >= 0)
        {
            $('.step2-validation-certificate .switch-row').append('<div class="col-md-3 col-sm-6 col-sx-12"><label class="switch"><input class="change-dcv-method" value="email" type="checkbox"><span class="slider round"></span><span class="label-text">{/literal}{$MGLANG->T('validaion_page', 'switch_email')}{literal}</span></label></div>');
        }
        if(availableMethod.indexOf('http') >= 0)
        {
            $('.step2-validation-certificate .switch-row').append('<div class="col-md-3 col-sm-6 col-sx-12"><label class="switch"><input class="change-dcv-method" value="http" type="checkbox"><span class="slider round"></span><span class="label-text">{/literal}{$MGLANG->T('validaion_page', 'switch_http')}{literal}</span></label></div>');
        }
        if(availableMethod.indexOf('https') >= 0)
        {
            $('.step2-validation-certificate .switch-row').append('<div class="col-md-3 col-sm-6 col-sx-12"><label class="switch"><input class="change-dcv-method" value="https" type="checkbox"><span class="slider round"></span><span class="label-text">{/literal}{$MGLANG->T('validaion_page', 'switch_https')}{literal}</span></label></div>');
        }
        if(availableMethod.indexOf('dns') >= 0)
        {
            $('.step2-validation-certificate .switch-row').append('<div class="col-md-3 col-sm-6 col-sx-12"><label class="switch"><input class="change-dcv-method" value="dns" type="checkbox"><span class="slider round"></span><span class="label-text">{/literal}{$MGLANG->T('validaion_page', 'switch_dns')}{literal}</span></label></div>');
        }

        $('.step2-validation-certificate').append('<table class="table"><thead><tr><th>{/literal}{$MGLANG->T('validaion_page', 'domain')}{literal}</th><th>{/literal}{$MGLANG->T('validaion_page', 'dcv_method')}{literal}</th><th>{/literal}{$MGLANG->T('validaion_page', 'email')}{literal}</th></tr></thead><tbody></tbody></table>');

        {/literal}{foreach $approvers as $domain => $emails}{literal}

        $('.step2-validation-certificate table tbody').append('<tr><td>{/literal}{$domain}{literal}</td><td><select name="method[{/literal}{$domain}{literal}]" class="select_method form-control">'+optionsMethod+'</select></td><td><select name="approver[{/literal}{$domain}{literal}]" class="form-control select_email">{/literal}{$emails}{literal}</select></td></tr>');

        {/literal}{/foreach}{literal}

        $('.switch-row div.col-md-3:first-child input.change-dcv-method').prop('checked', true).trigger("change");

    });

</script>
{/literal}