<div class="mg-module">
    <link rel="stylesheet" href="{$assetsURL}/css/theme.css">
    <div id="mg-wrapper" class="module-container">
        <div class="row" id="MGAlerts" style="display: block;">
            {if $error}
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                    <p style="margin-bottom: 0;"><strong>{$error}</strong></p>
                </div>
            {/if}
            {if $success}
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                    <p style="margin-bottom: 0;"><strong>{$success|htmlspecialchars_decode}</strong></p>
                </div>
            {/if}
            <div style="display:none;" data-prototype="error">
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                    <strong></strong>
                    <a style="display:none;" class="errorID" href=""></a>
                </div>
            </div>
            <div style="display:none;" data-prototype="success">
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                    <strong></strong>
                </div>
            </div>
        </div>
        <div id="mg-container">
            {* The module output is HTML. WHMCS 9 HTML-escapes the variables a module returns before this template
               runs, so nofilter alone shows the page as text: decode that one layer back. Only when it arrived
               escaped - decoding raw HTML would undo the |escape the page templates use for client data. *}
            {if $content|trim|substr:0:4 == '&lt;'}
                {$content|htmlspecialchars_decode nofilter}
            {else}
                {$content nofilter}
            {/if}
        </div>
        {literal}
            <script type="text/javascript">
                $(window).on('resize', function () {
                    var height = $('.module-sidebar').height();
                    $('#mg-wrapper').css('min-height', height);
                });
                $(document).ready(function (){
                    let mgContent = $('.mg-module').parent('div').clone();
                    $('#domain').html(mgContent);
                });
            </script>
        {/literal}
    </div>
</div>
