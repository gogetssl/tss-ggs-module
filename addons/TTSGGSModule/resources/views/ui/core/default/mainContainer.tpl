<div class="modulesgarden-app-main-container" :class="rootElements && rootElements.length == 1 ? 'lu-single-element' : ''">
    <div class="lu-row"><i v-show="pageLoading" class="page_processing"></i></div>


    <component v-bind="element.slots" v-bind:is="element.name" v-bind:key="element.uniq" v-for="element in rootElements">
    </component>

    <div id="allDropDown"></div>
    <div class="lu-preloader-container lu-preloader-container--full-screen lu-preloader-container--overlay" v-show="pagePreLoader">
        <div class="lu-preloader lu-preloader--lg"></div>
    </div>
</div>


<div id="loadedTemplates"></div>
