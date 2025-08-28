<div class="lu-col-md-12">


    <div id="layers2">
        <div class="lu-app">
            <div class="lu-app-main">
                <div class="lu-app-main__body">
                    {$content}
                </div>
            </div>
        </div>
    </div>

    {include file='partials/css_assets.tpl'}
    {include file='partials/js_assets.tpl'}

    <script>
        if (typeof whmcsPostOriginal === 'undefined')
        {
            var whmcsPostOriginal = {};
            Object.assign(whmcsPostOriginal, WHMCS.http.jqClient);

            function whmcsPostWrapper(t, e, i, n)
            {
                if (e.indexOf('modop'))
                {
                    if (typeof mgPageController !== 'undefined' && typeof mgPageController.vueLoader !== 'undefined' && mgPageController.vueLoader !== false)
                    {
                        for (var key in mgPageController.vueLoader.$children)
                        {
                            if (!mgPageController.vueLoader.$children.hasOwnProperty(key))
                            {
                                continue;
                            }
                            mgPageController.vueLoader.$children[key].$destroy();
                            mgPageController.vueLoader.$children[key] = false;
                        }
                        mgPageController.vueLoader.$destroy();
                        mgPageController.vueLoader = false;

                        $(".lu-tooltip").remove();
                    }
                }

                return whmcsPostOriginal.post(t, e, i, n);
            }

            WHMCS.http.jqClient.post = whmcsPostWrapper;
        }
    </script>
</div>
