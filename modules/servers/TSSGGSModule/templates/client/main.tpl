{* The module's part of the client area service page: messages on top, then the page itself. The scripts of a page
   show further messages with $('#ModAlerts').alerts(...) (assets/js/client.js), which copies the hidden boxes below. *}
<div class="mod-module">
    <link rel="stylesheet" href="{$assetsURL}/css/theme.css">
    <section id="mod-wrapper" class="module-container">
        {* one message under the other: the theme's "row" would put them side by side *}
        <div id="ModAlerts" class="row" style="display: block;">
            {if $error}
                <div class="alert alert-danger" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{$error}</strong>
                </div>
            {/if}
            {if $success}
                <div class="alert alert-success" role="status">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>{$success|htmlspecialchars_decode}</strong>
                </div>
            {/if}
            <div data-prototype="error" hidden>
                <div class="alert alert-danger" role="alert">
                    <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong></strong>
                </div>
            </div>
            <div data-prototype="success" hidden>
                <div class="alert alert-success" role="status">
                    <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong></strong>
                </div>
            </div>
        </div>
        <div id="mod-container">
            {* WHMCS 9 HTML-escapes the variables a module returns before this template runs: a page that arrives
               escaped is decoded back once. Raw HTML is never decoded (the pages escape client data themselves). *}
            {if $content|trim|substr:0:4 == '&lt;'}
                {$content|htmlspecialchars_decode nofilter}
            {else}
                {$content nofilter}
            {/if}
        </div>
    </section>
    {literal}
    <script>
        jQuery(function ($) {
            // the service page of some client area themes keeps the module output in its "domain" tab
            $('#domain').html($('.mod-module').parent('div').clone());
        });
    </script>
    {/literal}
</div>
