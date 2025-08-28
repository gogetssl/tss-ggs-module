<div class="change-approver-ssl-unified">
    <form method="post">
        <h2 class="card-title">{$MGLANG->T('change_approval', 'Change a Validation Method')}</h2>

        <div class="tab-content pt-3">
            <div class="step2-validation-certificate">
                <div class="alert alert-info" role="alert">
                    <p>{$MGLANG->T('change_approval', 'desc1')}</p>
                    <p></p>
                    <p><b>{$MGLANG->T('change_approval', 'Email Address')}: </b>{$MGLANG->T('change_approval', 'desc2')}</p>
                    <p></p>
                    <p><b>{$MGLANG->T('change_approval', 'DNS')}: </b>{$MGLANG->T('change_approval', 'desc3')}</p>
                    <p></p>
                    <p><b>{$MGLANG->T('change_approval', 'HTTP FILE')}: </b>{$MGLANG->T('change_approval', 'desc4')}</p>
                </div>
                <div class="row switch-row" style="margin:20px 0;">
                    {if 'email'|in_array:$available_method_validation}
                    <div class="col-md-3 col-sm-6 col-sx-12">
                        <label class="switch"><input class="change-dcv-method" value="email" type="checkbox">
                            <span class="slider round"></span>
                            <span class="label-text">{$MGLANG->T('change_approval', 'EMAIL for all')}</span>
                        </label>
                    </div>
                    {/if}
                    {if 'http'|in_array:$available_method_validation}
                    <div class="col-md-3 col-sm-6 col-sx-12">
                        <label class="switch"><input class="change-dcv-method" value="http" type="checkbox">
                            <span class="slider round"></span>
                            <span class="label-text">{$MGLANG->T('change_approval', 'HTTP for all')}</span>
                        </label>
                    </div>
                    {/if}
                    {if 'https'|in_array:$available_method_validation}
                    <div class="col-md-3 col-sm-6 col-sx-12">
                        <label class="switch"><input class="change-dcv-method" value="https" type="checkbox">
                            <span class="slider round"></span>
                            <span class="label-text">{$MGLANG->T('change_approval', 'HTTPs for all')}</span>
                        </label>
                    </div>
                    {/if}
                    {if 'dns'|in_array:$available_method_validation}
                    <div class="col-md-3 col-sm-6 col-sx-12">
                        <label class="switch"><input class="change-dcv-method" value="dns" type="checkbox">
                            <span class="slider round"></span>
                            <span class="label-text">{$MGLANG->T('change_approval', 'DNS for all')}</span>
                        </label>
                    </div>
                    {/if}
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>{$MGLANG->T('change_approval', 'Domain')}</th>
                            <th>{$MGLANG->T('change_approval', 'DCV Method')}</th>
                            <th>{$MGLANG->T('change_approval', 'Email Address')}</th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach $domains as $domain}
                        <tr>
                            <td>{$domain}</td>
                            <td>
                                <select name="method[{$domain}]" class="select_method form-control">
                                    {if 'email'|in_array:$available_method_validation}<option value="email">EMAIL</option>{/if}
                                    {if 'dns'|in_array:$available_method_validation}<option value="dns">DNS</option>{/if}
                                    {if 'http'|in_array:$available_method_validation}<option value="http">HTTP</option>{/if}
                                    {if 'https'|in_array:$available_method_validation}<option value="https">HTTPS</option>{/if}
                                </select>
                            </td>
                            <td>
                                <select name="approver[{$domain}]" class="form-control select_email">
                                    {$approvers[$domain]}
                                </select>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{$MGLANG->T('change_approval', 'Change')}</button>
    </form>
</div>

{literal}
<style>
    .card-body {
        overflow: hidden!important;
    }
    .label-text{position: absolute;width: 200px;left: 70px;top: 5px;cursor: pointer;}.switch{position:relative;display:block;width:60px;height:34px}.switch input{opacity:0;width:0;height:0}.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;-webkit-transition:.4s;transition:.4s}.slider:before{position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;background-color:#fff;-webkit-transition:.4s;transition:.4s}input:checked+.slider{background-color:#369}input:focus+.slider{box-shadow:0 0 1px #2196f3}input:checked+.slider:before{-webkit-transform:translateX(26px);-ms-transform:translateX(26px);transform:translateX(26px)}.slider.round{border-radius:34px}.slider.round:before{border-radius:50%}</style>
{/literal}

<script>

    $(document).ready(function (){

        $('#Primary_Sidebar-Service_Details_Overview-Information').removeClass('active');
        $('#Primary_Sidebar-Service_Details_Actions-ChangeApprovalMethod').addClass('active');

        let clone_element = $('.change-approver-ssl-unified').clone();
        $('.product-details').html(clone_element);
        $('.mg-module .change-approver-ssl-unified').remove();
        $('.product-details-tab-container').hide();
        $('#tabOverview .nav-tabs').hide();
    });

</script>

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


        $('.switch-row div.col-md-3:first-child input.change-dcv-method').prop('checked', true).trigger("change");

    });

</script>