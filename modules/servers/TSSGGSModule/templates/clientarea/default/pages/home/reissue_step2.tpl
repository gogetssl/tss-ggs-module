<div class="reissue-ssl-unified">

    <div class="card-body" style="overflow: hidden;">
        <form method="post">

            <input type="hidden" name="customAction" value="sendReissue">
            {foreach $postData as $key => $value}
                {if $key != 'token'}
                    <input type="hidden" name="{$key}" value="{$value}">
                {/if}
            {/foreach}

            <h2 class="card-title">{$MGLANG->T('reissue_page', 'Reissue - Select a Validation Method')}</h2>

            <div class="pt-3">
                <div class="step2-validation-certificate">
                    <div class="alert alert-info" role="alert">
                        <p>{$MGLANG->T('reissue_page', 'desc_validation')}</p>
                        <p></p>
                        <p><b>{$MGLANG->T('reissue_page', 'Email Address')} </b>{$MGLANG->T('reissue_page', 'The email to send the validation to is generated from a default list of addresses.')}</p>
                        <p></p>
                        <p><b>{$MGLANG->T('reissue_page', 'DNS')}: </b>{$MGLANG->T('reissue_page', 'Add a DNS CNAME record using the hash-values provided.')}</p>
                        <p></p>
                        <p><b>{$MGLANG->T('reissue_page', 'HTTP FILE')}: </b>{$MGLANG->T('reissue_page', 'Create a text file to be served from the domain name requested.')}</p>
                    </div>
                    <div class="row switch-row" style="margin:20px 0;">
                        <div class="col-md-3 col-sm-6 col-sx-12">
                            <label class="switch">
                                <input class="change-dcv-method" value="email" type="checkbox">
                                <span class="slider round"></span>
                                <span class="label-text">{$MGLANG->T('reissue_page', 'EMAIL for all')}</span>
                            </label>
                        </div>
                        <div class="col-md-3 col-sm-6 col-sx-12">
                            <label class="switch">
                                <input class="change-dcv-method" value="http" type="checkbox">
                                <span class="slider round"></span>
                                <span class="label-text">{$MGLANG->T('reissue_page', 'HTTP for all')}</span>
                            </label>
                        </div>
                        <div class="col-md-3 col-sm-6 col-sx-12">
                            <label class="switch">
                                <input class="change-dcv-method" value="https" type="checkbox">
                                <span class="slider round"></span>
                                <span class="label-text">{$MGLANG->T('reissue_page', 'HTTPs for all')}</span>
                            </label>
                        </div>
                        <div class="col-md-3 col-sm-6 col-sx-12">
                            <label class="switch">
                                <input class="change-dcv-method" value="dns" type="checkbox">
                                <span class="slider round"></span>
                                <span class="label-text">{$MGLANG->T('reissue_page', 'DNS for all')}</span>
                            </label>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{$MGLANG->T('reissue_page', 'Domain')}</th>
                                <th>{$MGLANG->T('reissue_page', 'DCV Method')}</th>
                                <th>{$MGLANG->T('reissue_page', 'Email Address')}</th>
                            </tr>
                            </thead>
                        <tbody>
                        {foreach $approvers as $domain => $emails}
                            <tr>
                                <td>{$domain}</td>
                                <td>
                                    <select name="method[{$domain}]" class="select_method form-control">
                                        <option value="email">EMAIL</option>
                                        <option value="dns">DNS</option>
                                        <option value="http">HTTP</option>
                                        <option value="https">HTTPS</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="approver[{$domain}]" class="form-control select_email">
                                        {$emails}
                                    </select>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary" id="btnOrderContinue">
                    {$MGLANG->T('reissue_page', 'Click to Continue')}
                </button>
            </div>

        </form>
    </div>
</div>
{literal}
<style>.label-text{position: absolute;width: 200px;left: 70px;top: 5px;cursor: pointer;}.switch{position:relative;display:block;width:60px;height:34px}.switch input{opacity:0;width:0;height:0}.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;-webkit-transition:.4s;transition:.4s}.slider:before{position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;background-color:#fff;-webkit-transition:.4s;transition:.4s}input:checked+.slider{background-color:#369}input:focus+.slider{box-shadow:0 0 1px #2196f3}input:checked+.slider:before{-webkit-transform:translateX(26px);-ms-transform:translateX(26px);transform:translateX(26px)}.slider.round{border-radius:34px}.slider.round:before{border-radius:50%}</style>
{/literal}
<script>
    $(document).ready(function (){
        let clone_element = $('.mg-module').clone();
        $('.product-details').html(clone_element);

        $('.tab-pane .tab-content').remove();
        $('.tab-pane .nav-tabs').remove();

        $('#Primary_Sidebar-Service_Details_Overview-Information').removeClass('active');
        $('#Primary_Sidebar-Service_Details_Actions-ReissueSSL').addClass('active');


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

        $('.switch-row div.col-md-3:first-child input.change-dcv-method').prop('checked', true).trigger("change");

    });
</script>