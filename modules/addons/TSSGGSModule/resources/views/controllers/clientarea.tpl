<div class="mg-wrapper body lu-bg-transparent clientarea" data-target=".body" data-spy="scroll" data-twttr-rendered="true">
    <div class="full-screen-module-container" id="layers2">
        <div class="lu-app lu-bg-transparent" id="{$vueInstanceName}">
            <component v-if="rootElements.navbar" v-bind="rootElements.navbar.slots" v-bind:is="rootElements.navbar.name" v-bind:key="rootElements.navbar.uniq"></component>

            <div class="lu-app-main">
                <div class="lu-app-main__body lu-app-clientarea">
                    <component v-if="rootElements.breadcrumb" v-bind="rootElements.breadcrumb.slots" v-bind:is="rootElements.breadcrumb.name" v-bind:key="rootElements.breadcrumb.uniq"></component>

                    <div :class="rootElements && rootElements.alerts && rootElements.alerts.length == 1 ? 'lu-single-element' : ''">
                        <component v-if="rootElements.alerts" v-bind="alert.slots" v-bind:is="alert.name" v-bind:key="alert.uniq" v-for="alert in rootElements.alerts"></component>
                    </div>

                    <div class="modulesgarden-app-main-container" :class="rootElements && rootElements.body && rootElements.body.length == 1 ? 'lu-single-element' : ''">
                        <div class="lu-row"><i v-show="pageLoading" class="page_processing"></i></div>

                        <component v-bind="element.slots" v-bind:is="element.name" v-bind:key="element.uniq" v-for="element in rootElements.body"></component>

                        <div class="lu-preloader-container lu-preloader-container--full-screen lu-preloader-container--overlay" v-show="pagePreLoader">
                            <div class="lu-preloader lu-preloader--lg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="clear"></div>

{include file='partials/css_assets.tpl'}
{include file='partials/js_assets.tpl'}




