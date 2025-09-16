<style>
    .csr_details_validation {
        border: 1px solid #ddd;
        padding: 20px;
        margin-top:20px;
        background-color: #f9f9f9;
        border-radius: 5px;
        margin-bottom: 25px;
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
    $(document).ready(function(){

        {if $enable_csr}

        let countries = {$countries};
        let countryOptions = '';
        for (let country_code in countries) {
            countryOptions += '<option value="' + country_code + '">'+ countries[country_code]['name'] + '</option>'
        }

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
                $('#csrGeneration .modal-body').prepend('<div class="alert alert-danger">{$MGLANG->T('stepone', 'please_fill')}</div>');
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
                url: "clientarea.php?action=productdetails&id={$serviceId}&mg-page=home&mg-action=generateCSR&json=1",
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
                    $('#generateCSRFunc').html('{$MGLANG->T('stepone_modal', 'loading')}');
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

                    $('#generateCSRForm').html('{$MGLANG->T('stepone_modal', 'generate')}');
                    $('#generateCSRForm').prop('disabled', false);

                });

        }

        function downloadPkey()
        {
            window.open('clientarea.php?action=productdetails&id={$serviceId}&mg-page=home&mg-action=downloadKey&pkey='+$('#pkeyField').val(), '_blank').focus();
        }

        {/if}

        function checkCSR(csrValue) {
            $.ajax({
                url: "clientarea.php?action=productdetails&id={$serviceId}&mg-page=home&mg-action=checkCSR&json=1",
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

        {if $post}
            let csrValue = $('#inputCsr').val();
            checkCSR(csrValue);
        {/if}

        $('body').on('input', '#inputCsr', function (){
            let csrValue = $(this).val();
            checkCSR(csrValue);
        });

        {if $enable_csr}

        $('body').on('click', '#downloadPkey', function (){
            downloadPkey();
        });

        $('body').on('click', '#generateCSRFunc', function (){
            generateCSR();
        });

        {/if}

        $('<div class="alert alert-warning csr_validation_error" style="margin-top:10px;display:none">{$MGLANG->T('csr_details', 'error')}</div>' +
            '<div class="csr_details_validation" style="display:none">' +
            '<h4>{$MGLANG->T('csr_details', 'title')}</h4>' +
            '<div class="row">' +
            '<div class="col-sm-6 col-xs-12">' +
            '<p>{$MGLANG->T('csr_details', 'commonName')} <span class="commonName"></span></p>' +
            '<p>{$MGLANG->T('csr_details', 'company')} <span class="companyName"></span></p>' +
            '<p>{$MGLANG->T('csr_details', 'state')} <span class="stateName"></span></p>' +
            '<p style="border:0;">{$MGLANG->T('csr_details', 'country')} <span class="countryName"></span></p>' +
            '</div>' +
            '<div class="col-sm-6 col-xs-12">' +
            '<p>{$MGLANG->T('csr_details', 'email')} <span class="emailName"></span></p>' +
            '<p>{$MGLANG->T('csr_details', 'division')} <span class="divisionName"></span></p>' +
            '<p>{$MGLANG->T('csr_details', 'city')} <span class="cityName"></span></p>' +
            '</div>' +
            '</div>' +
            '</div>').insertAfter("#inputCsr");

        {if $enable_csr}

        $('<button type="button" data-toggle="modal" data-target="#csrGeneration" class="btn btn-primary" style="margin-top:10px;">{$MGLANG->T('stepone', 'generate_csr')}</button>'+
            '<button type="button" id="downloadPkey" class="btn btn-default" style="margin-left:10px;margin-top:10px;display:none;">{$MGLANG->T('stepone', 'download_pkey')}</button>'+
            '<input type="hidden" id="pkeyField" value="">'+
            '<div class="modal fade" id="csrGeneration" tabindex="-1" role="dialog">'+
                '<div class="modal-dialog" role="document">'+
                '<div class="modal-content">'+
                    '<div class="modal-header" style="display:block;">'+
                        '<button style="float:right;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                        '<h4 style="float:left;" class="modal-title">{$MGLANG->T('stepone_modal', 'generate_csr')}</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                        '<div class="form-group">'+
                            '<label class="control-label" for="C">{$MGLANG->T('stepone_modal', 'country')}</label>'+
                            '<select class="form-control generateCsrInput" id="countryName" name="C">'+countryOptions+'</select>'+
                        '</div>'+
                        '<div class="form-group">'+
                              '<label class="control-label" for="ST">{$MGLANG->T('stepone_modal', 'state')}</label>'+
                              '<input {if $use_profile_data}value="{$client.state}"{/if} class="form-control generateCsrInput" id="stateOrProvinceName" placeholder="{$MGLANG->T('stepone_modal', 'state_p')}" name="ST" type="text">'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<label class="control-label" for="L">{$MGLANG->T('stepone_modal', 'locality')}</label>'+
                            '<input {if $use_profile_data}value="{$client.city}"{/if} class="form-control generateCsrInput" value="" id="localityName" placeholder="{$MGLANG->T('stepone_modal', 'locality_p')}" name="L" value="" type="text">'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<label class="control-label" for="O">{$MGLANG->T('stepone_modal', 'organization')}</label>'+
                            '<input {if $use_profile_data}value="{$client.companyname}"{/if} class="form-control generateCsrInput" id="organizationName" placeholder="{$MGLANG->T('stepone_modal', 'organization_p')}" name="O" value="" type="text">'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<label class="control-label" for="OU">{$MGLANG->T('stepone_modal', 'org_unit')}</label>'+
                            '<input {if $use_profile_data}value="IT"{/if} class="form-control generateCsrInput" id="organizationalUnitName" placeholder="{$MGLANG->T('stepone_modal', 'org_unit_p')}" value="" type="text">'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<label class="control-label" for="CN">{$MGLANG->T('stepone_modal', 'common_name')}</label>'+
                            '<input {if $use_profile_data}value="{$domain}"{/if} class="form-control generateCsrInput" autocomplete="off" id="commonName" placeholder="{$MGLANG->T('stepone_modal', 'common_name_p')}" name="CN" value="" type="text">'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<label class="control-label" for="EA">{$MGLANG->T('stepone_modal', 'email')}</label>'+
                            '<input {if $use_profile_data}value="{$client.email}"{/if} class="form-control generateCsrInput" id="emailAddress" placeholder="{$MGLANG->T('stepone_modal', 'email_p')}" name="EA" value="" type="text">'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                        '<button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('stepone_modal', 'close')}</button>'+
                        '<button type="button" id="generateCSRFunc" class="btn btn-primary">{$MGLANG->T('stepone_modal', 'generate')}</button>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>').insertAfter("#inputCsr");

            {if $default_csr_country}

            $('#countryName').val('{$default_csr_country}');

            {/if}

            {if $use_profile_data}

            $('#countryName').val('{$client.country}');

            {/if}

        {/if}

        $('input[name="fields[js_custom]"]').hide();
        $('textarea[name="fields[sans_domains]"]').addClass("form-control");
        $('textarea[name="fields[wildcard_san]"]').addClass("form-control");
        $("#inputCsr").parents(".card-body").find(".alert-info").text("{$MGLANG->T('csr_details', 'description')}");
        $("#inputServerType").parent(".form-group").hide();
        $("#inputServerType").val("1000");

        {if $adminFormHidden}

            $('#inputJobTitle').val('IT');
            $('#inputFirstName').parents('fieldset').prev('div').prev('h2').hide();
            $('#inputFirstName').parents('fieldset').prev('div').hide();
            $('#inputFirstName').parents('fieldset').hide();

        {/if}


    });
</script>