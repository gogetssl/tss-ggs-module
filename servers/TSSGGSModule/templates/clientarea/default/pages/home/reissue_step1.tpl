<div class="reissue-ssl-unified">


    <div class="card-body">

        {if $errors}
            <div class="alert alert-danger">
                {$errors}
            </div>
        {/if}

        <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}&mg-action=reissueSSL&step=two">

            <h2 class="card-title">{$MGLANG->T('reissue_page', 'Reissue Certificate')}</h2>

            <div class="alert alert-info">{$MGLANG->T('reissue_page', 'desc_page')}</div>

            <div class="form-group">
                <label for="inputCsr" class="text-md-right">{$MGLANG->T('reissue_page', 'Certificate Signing Request')}</label>

                {if $post_csr}
                    <textarea name="csr" id="inputCsr" rows="7" class="form-control">{$post_csr}</textarea>
                {else}
                    <textarea name="csr" id="inputCsr" rows="7" class="form-control">-----BEGIN CERTIFICATE REQUEST-----

        -----END CERTIFICATE REQUEST-----</textarea>
                {/if}

                {if $enable_csr}
                    <button type="button" data-toggle="modal" data-target="#csrGeneration" class="btn btn-primary" style="margin-top:10px;">{$MGLANG->T('reissue_page', 'Generate CSR')}</button>
                    <button type="button" id="downloadPkey" class="btn btn-default" style="margin-left:10px;margin-top:10px;display:none;">{$MGLANG->T('reissue_page', 'Download Private Key')}</button>
                    <input type="hidden" id="pkeyField" value="">

                    <div class="modal fade" id="csrGeneration" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="display:block;">
                                    <button style="float:right;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                    <h4 style="float:left;" class="modal-title">{$MGLANG->T('reissue_page', 'Generate CSR')}</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="control-label" for="C">{$MGLANG->T('reissue_page', 'Country')}</label>
                                        <select class="form-control generateCsrInput" id="countryName" name="C">
                                            {foreach $countries as $code => $data}
                                                <option value="{$code}">{$data.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="ST">{$MGLANG->T('reissue_page', 'State or Province')}</label>
                                        <input {if $use_profile_data}value="{$client.state}"{/if} class="form-control generateCsrInput" id="stateOrProvinceName" placeholder="State or Province" name="ST" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="L">{$MGLANG->T('reissue_page', 'Locality')}</label>
                                        <input {if $use_profile_data}value="{$client.city}"{/if} class="form-control generateCsrInput" id="localityName" placeholder="Locality" name="L" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="O">{$MGLANG->T('reissue_page', 'Orgarnization')}</label>
                                        <input {if $use_profile_data}value="{$client.companyname}"{/if} class="form-control generateCsrInput" id="organizationName" placeholder="Organization" name="O" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="OU">{$MGLANG->T('reissue_page', 'Organization Unit')}</label>
                                        <input {if $use_profile_data}value="IT"{/if} class="form-control generateCsrInput" id="organizationalUnitName" placeholder="Organization Unit" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="CN">{$MGLANG->T('reissue_page', 'Common Name')}</label>
                                        <input {if $use_profile_data}value="{$domain}"{/if} class="form-control generateCsrInput" autocomplete="off" id="commonName" placeholder="Common Name" name="CN" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="EA">{$MGLANG->T('reissue_page', 'Email')}</label>
                                        <input {if $use_profile_data}value="{$client.email}"{/if} class="form-control generateCsrInput" id="emailAddress" placeholder="Email" name="EA" type="text">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('reissue_page', 'Close')}</button>
                                    <button type="button" id="generateCSRFunc" class="btn btn-primary">{$MGLANG->T('reissue_page', 'Generate')}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                {/if}

                <div class="alert alert-warning csr_validation_error" style="margin-top:10px;display:none">{$MGLANG->T('reissue_page', 'CSR is invalid. Please check your CSR and try again')}</div>
                <div class="csr_details_validation" style="display:none">
                    <h4>{$MGLANG->T('reissue_page', 'CSR Details')}</h4>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <p>{$MGLANG->T('reissue_page', 'Common Name (CN)')}: <span class="commonName"></span></p>
                            <p>{$MGLANG->T('reissue_page', 'Company')}: <span class="companyName"></span></p>
                            <p>{$MGLANG->T('reissue_page', 'State')}: <span class="stateName"></span></p>
                            <p style="border:0;">{$MGLANG->T('reissue_page', 'Country')}: <span class="countryName"></span></p>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <p>{$MGLANG->T('reissue_page', 'Email')}: <span class="emailName"></span></p>
                            <p>{$MGLANG->T('reissue_page', 'Division')}: <span class="divisionName"></span></p>
                            <p>{$MGLANG->T('reissue_page', 'City')}: <span class="cityName"></span></p>
                        </div>
                    </div>
                </div>

            </div>


            {if $sans || $sans_wildcard}
                <div><strong>{$MGLANG->T('reissue_page', 'Subject Alternative Names (SANs)')}</strong></div>
            {/if}

            {if $sans}
                <fieldset>
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-md-right" for="inputAdditionalField">{$lang_san}</label>
                        <div class="col-md-8">
                            <textarea name="fields[sans_domains]" cols="60" rows="" class="form-control">{$post_san}</textarea> {$MGLANG->T('reissue_page', 'desc_san')}
                        </div>
                    </div>
                </fieldset>
            {/if}

            {if $sans_wildcard}
                <fieldset>
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-md-right" for="inputAdditionalField">{$lang_san_wildcard}</label>
                        <div class="col-md-8">
                            <textarea name="fields[sans_domains_wildcard]" cols="60" rows="" class="form-control">{$post_san_wildcard}</textarea> {$MGLANG->T('reissue_page', 'desc_san_wildcard')}
                        </div>
                    </div>
                </fieldset>
            {/if}

            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    {$MGLANG->T('reissue_page', 'Click to Continue')}
                </button>
            </div>

        </form>

    </div>

</div>

<style>
    .csr_details_validation {
        border: 1px solid #ddd;
        padding:20px;
        margin-top:20px;
        background-color: #f9f9f9;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    .csr_details_validation h4 {
        font-weight: 600;
        font-size:19px;
        margin-bottom: 15px;
    }
    .csr_details_validation p {
        padding-bottom:5px;
        margin-bottom:5px;
        letter-spacing: 0.1px;
        border-bottom: 1px solid #ddd;
        font-size:15px;
        font-weight: 700;
    }
    .csr_details_validation p span{
        font-weight: 500;
    }
    .has-error label {
        color: #a94442;
    }
    .has-error input {
        border-color: #a94442;
    }
</style>

<script>

    $(document).ready(function (){
        let clone_element = $('.mg-module').clone();
        $('.product-details').html(clone_element);

        $('.tab-pane .tab-content').remove();
        $('.tab-pane .nav-tabs').remove();

        $('#Primary_Sidebar-Service_Details_Overview-Information').removeClass('active');
        $('#Primary_Sidebar-Service_Details_Actions-ReissueSSL').addClass('active');
    });

    $(document).ready(function(){

        {if $default_csr_country}

        $('#countryName').val('{$default_csr_country}');

        {/if}

        {if $use_profile_data}

        $('#countryName').val('{$client.country}');

        {/if}


        function validateFormCSR()
        {
            let validate = true;

            let C = $('#countryName').val();
            let SP = $('#stateOrProvinceName').val();
            let L = $('#localityName').val();
            let O = $('#organizationName').val();
            let UN = $('#organizationalUnitName').val();
            let CN = $('#commonName').val();
            let EA = $('#emailAddress').val();

            if(SP == '')
            {
                validate = false;
                $('#stateOrProvinceName').parent('.form-group').addClass('has-error');
            }
            else
            {
                $('#stateOrProvinceName').parent('.form-group').removeClass('has-error');
            }

            if(L == '')
            {
                validate = false;
                $('#localityName').parent('.form-group').addClass('has-error');
            }
            else
            {
                $('#localityName').parent('.form-group').removeClass('has-error');
            }

            if(O == '')
            {
                validate = false;
                $('#organizationName').parent('.form-group').addClass('has-error');
            }
            else
            {
                $('#organizationName').parent('.form-group').removeClass('has-error');
            }

            if(UN == '')
            {
                validate = false;
                $('#organizationalUnitName').parent('.form-group').addClass('has-error');
            }
            else
            {
                $('#organizationalUnitName').parent('.form-group').removeClass('has-error');
            }

            if(CN == '')
            {
                validate = false;
                $('#commonName').parent('.form-group').addClass('has-error');
            }
            else
            {
                $('#commonName').parent('.form-group').removeClass('has-error');
            }

            if(EA == '')
            {
                validate = false;
                $('#emailAddress').parent('.form-group').addClass('has-error');
            }
            else
            {
                $('#emailAddress').parent('.form-group').removeClass('has-error');
            }


            if(validate == true)
            {
                return true;
            }
            else
            {
                $('#csrGeneration .modal-body .alert-danger').remove();
                $('#csrGeneration .modal-body').prepend('<div class="alert alert-danger">Please fill the fields</div>');
            }

            return false;
        }

        function generateCSR() {

            if(validateFormCSR() == false)
            {
                return false;
            }

            let C = $('#countryName').val();
            let SP = $('#stateOrProvinceName').val();
            let L = $('#localityName').val();
            let O = $('#organizationName').val();
            let UN = $('#organizationalUnitName').val();
            let CN = $('#commonName').val();
            let EA = $('#emailAddress').val();

            $.ajax({
                url: "clientarea.php?action=productdetails&id=68&mg-page=home&mg-action=generateCSR&json=1",
                method: "POST",
                dataType: "json",
                data: {
                    country: C,
                    state: SP,
                    locality: L,
                    organization: O,
                    unit_name: UN,
                    common_name: CN,
                    email_address: EA
                },
                beforeSend: function () {
                    $('#generateCSRFunc').html('Loading...');
                    $('#generateCSRFunc').prop('disabled', true);
                }
            })

                .done(res => {

                    if(res.data.json.status == 'success') {
                        let newCSR = res.data.json.data.csr;
                        let newPkey = res.data.json.data.private_key;
                        $('#inputCsr').val(newCSR).trigger('input');
                        $('#csrGeneration').modal('hide')
                        $('#downloadPkey').show();
                        $('#pkeyField').val(newPkey);
                    }
                    else
                    {
                        $('#csrGeneration .modal-body .alert-danger').remove();
                        $('#csrGeneration .modal-body').prepend('<div class="alert alert-danger">'+res.data.json.message+'</div>');
                    }

                    $('#generateCSRForm').html('Generate');
                    $('#generateCSRForm').prop('disabled', false);

                });

        }

        function downloadPkey()
        {
            window.open('clientarea.php?action=productdetails&id=68&mg-page=home&mg-action=downloadKey&pkey='+$('#pkeyField').val(), '_blank').focus();
        }


        function checkCSR(csrValue) {
            $.ajax({
                url: "clientarea.php?action=productdetails&id=68&mg-page=home&mg-action=checkCSR&json=1",
                method: "POST",
                dataType: "json",
                data: {
                    csr: csrValue
                },
                beforeSend: function() {
                    $("#fullpage-overlay").show();
                    $('.csr_validation_error').hide();
                }
            })

                .done(res => {
                    $("#fullpage-overlay").hide();
                    if(res.data.json.data === false)
                    {
                        $('.csr_validation_error').show();
                        $('.csr_details_validation').hide();
                    }
                    else
                    {
                        $('.commonName').text(res.data.json.data.CN);
                        $('.companyName').text(res.data.json.data.O);
                        $('.stateName').text(res.data.json.data.ST);
                        $('.countryName').text(res.data.json.data.C);
                        $('.emailName').text(res.data.json.data.E);
                        $('.divisionName').text(res.data.json.data.OU);
                        $('.cityName').text(res.data.json.data.L);
                        $('.emailName').text(res.data.json.data.emailAddress);
                        $('.csr_details_validation').show();
                    }
                });
        }


        $('body').on('input', '#inputCsr', function (){
            let csrValue = $(this).val();
            checkCSR(csrValue);
        });


        $('body').on('click', '#downloadPkey', function (){
            downloadPkey();
        });

        $('body').on('click', '#generateCSRFunc', function (){
            generateCSR();
        });

    });

</script>