<div class="manage-ssl-unified">

    <h2>{$MGLANG->T('awaiting_configuration', 'Manage Product')}</h2>
    <div class="table-unified-information">
        <p class="desc">{$product_title}</p>
        <div class="row">
            <div class="col-sm-6 col-xs-12">
                <div class="row-unified" style="border-right:2px solid #f8e7aa">
                    <h4>{$product_name}</h4>
                    <table>
                        <tbody>
                        <tr>
                            <td>{$MGLANG->T('awaiting_configuration', 'order_number')}</td>
                            <td>{$order_number}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('awaiting_configuration', 'Domain Name')}</td>
                            <td>{if $domain}{$domain}{else}{$MGLANG->T('awaiting_configuration', 'N/A')}{/if}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('awaiting_configuration', 'Status')}</td>
                            <td class="ready-to-use">{$MGLANG->T('awaiting_configuration', 'Ready to set up')}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('awaiting_configuration', 'Valid from')}</td>
                            <td>{$MGLANG->T('awaiting_configuration', 'N/A')}</td>
                        </tr>
                        <tr>
                            <td>{$MGLANG->T('awaiting_configuration', 'Valid to')}</td>
                            <td>{$MGLANG->T('awaiting_configuration', 'N/A')}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-sm-6 col-xs-12">
                <div class="row-unified">
                    <h4>{$MGLANG->T('awaiting_configuration', 'Before validation')}</h4>
                    <p class="desc_row">{$MGLANG->T('awaiting_configuration', 'desc')}</p>
                    <a href="{$url}" class="btn-set-up-unified">{$MGLANG->T('awaiting_configuration', 'Set Up')}</a>
                </div>
            </div>
        </div>
    </div>
</div>


<script>

    $(document).ready(function (){
        let clone_element = $('.mg-module').clone();
        $('.product-details').html(clone_element);

        $('.tab-pane .tab-content').remove();
        $('.tab-pane .nav-tabs').remove();

    });

</script>