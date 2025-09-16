{*<script type="text/javascript" src="{$assetsURL}/js/external/vue.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/vuex.min.js"></script>*}
{*<script>*}

{*    console.log("aswedfghjk")*}
{*    if (!Vue) {*}
{*        console.log("weszlo")*}
{*        let script = document.createElement('script');*}
{*        document.getElementById("main").appendChild(script);*}
{*        script.src = "{$assetsURL}/js/external/vuex.min.js";*}
{*    } else {*}
{*        console.log(" nie weszlo")*}
{*    }*}
{*</script>*}

{*<script type="text/javascript" src="{$assetsURL}/js/external/axios.min.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/moment.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/jscolor.min.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/layers2-ui.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/layers2-ui-table.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/chart.min.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/vue-cookies.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/sortable.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/vue-draggable.js"></script>*}
{*<link rel="stylesheet" href="{$assetsURL}/js/external//simplemde.min.css">*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/simplemde.min.js"></script>*}
{*<script type="text/javascript" src="{$assetsURL}/js/external/marked.min.js"></script>*}


<script>
    var {$moduleName} = {
        vueContainerId: "{$vueInstanceName}",
        vueStoreData: {$vueStoreData},
        currentUrl: "{$currentUrl}",
        componentsUrl: "{$componentsUrl}",
        extraParams: {$extraParams},
        rootElements: {$rootElements}
    }
</script>

<script type="text/javascript" src="{$assetsURL}/../../build/components.php?v={$moduleVersion}"></script>