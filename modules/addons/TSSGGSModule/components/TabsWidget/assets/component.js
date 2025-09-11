var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'css',
            'content',
            'title',
            'elements',
            'disableSwiper',
        ],
        data: function () {
            return {
                elements_: [],
                css_: [],
                title_: '',
                content_: '',
                selectedTabId: '',
                erroredTab: null,
                disableSwiper_: false,
            };
        },
        created: function () {
            if (typeof this.elements_.tabs == "object" && this.elements_.tabs.length > 0) {
                this.elements_.tabs = this.elements_.tabs.filter((tab) =>  typeof tab == "object" && Object.keys(tab).length > 0);
            }

            this.initSelectedTab();
        },
        mounted: function()
        {
            this.$nextTick(function () {
                this.initSwiper();
            });
        },
        methods: {
            onReloadParent: function () {
                this.onReload();
            },
            initSelectedTab: function () {
                if (typeof this.elements_.tabs == "undefined" || this.elements_.tabs.length === 0)
                {
                    return;
                }

                this.setActiveTab(this.elements_.tabs[0].cid);
            },
            onSetActiveTab: function (tabId) {
                this.setActiveTab(tabId);
            },
            onSetErroredTab: function (tabId) {
                if (this.erroredTab != null)
                {
                    return;
                }
                this.erroredTab = tabId;
                this.selectedTabId = tabId;
                this.triggerTabClick(tabId)
            },
            onResetErroredTab: function () {
                this.erroredTab = null
            },
            onClickMiddle: function (event) {
                event.target.closest("a").removeAttribute("href");
                window.open(window. location.href, "_blank")
            },
            setActiveTab: function (tabId) {
                this.selectedTabId = tabId;
            },
            triggerTabClick: function (tabId) {
                const tab = $('a[href$="' + tabId + '"]')

                if (tab.length > 0)
                {
                    tab.click();
                }
            },
            initSwiper: function () {

                const swiperContainer = document.getElementById(this.cid_).querySelector('.swiper-container');

                if (!swiperContainer || typeof swiperContainer === "undefined")
                {
                    return;
                }

                const swiper = swiperContainer.swiper;

                if (this.disableSwiper_)
                {
                    if (typeof swiper !== "undefined")
                    {
                        swiper.destroy();
                    }

                    return;
                }

                //already initialized
                if (typeof swiper !== "undefined")
                {
                    return;
                }

                //initialize swipe
                new Swiper(document.getElementById(this.cid_).querySelector('.swiper-container'), {
                    "scrollbarHide": true,
                    "effect": false,
                    "resistance": true,
                    "resistanceRatio": 0,
                    "slidesPerView": "auto",
                    "watchSlidesVisibility": true,
                    "direction": "horizontal",
                    "touchEventsTarget": "lu-container",
                    "initialSlide": 0,
                    "speed": 300,
                    "autoplay": false,
                    "autoplayDisableOnInteraction": true,
                    "autoplayStopOnLast": false,
                    "iOSEdgeSwipeDetection": false,
                    "iOSEdgeSwipeThreshold": 20,
                    "freeMode": false,
                    "freeModeMomentum": true,
                    "freeModeMomentumRatio": 1,
                    "freeModeMomentumBounce": true,
                    "freeModeMomentumBounceRatio": 1,
                    "freeModeMomentumVelocityRatio": 1,
                    "freeModeSticky": false,
                    "freeModeMinimumVelocity": 0.02,
                    "autoHeight": false,
                    "setWrapperSize": false,
                    "virtualTranslate": false,
                    "coverflow": {
                        "rotate": 50,
                        "stretch": 0,
                        "depth": 100,
                        "modifier": 1,
                        "slideShadows": true
                    },
                    "flip": {
                        "slideShadows": true,
                        "limitRotation": true
                    },
                    "cube": {
                        "slideShadows": true,
                        "shadow": true,
                        "shadowOffset": 20,
                        "shadowScale": 0.94
                    },
                    "fade": {
                        "crossFade": false
                    },
                    "parallax": false,
                    "zoom": false,
                    "zoomMax": 3,
                    "zoomMin": 1,
                    "zoomToggle": true,
                    "scrollbar": null,
                    "scrollbarDraggable": false,
                    "scrollbarSnapOnRelease": false,
                    "keyboardControl": false,
                    "mousewheelControl": false,
                    "mousewheelReleaseOnEdges": false,
                    "mousewheelInvert": false,
                    "mousewheelForceToAxis": false,
                    "mousewheelSensitivity": 1,
                    "mousewheelEventsTarged": "lu-container",
                    "hashnav": false,
                    "hashnavWatchState": false,
                    "history": false,
                    "replaceState": false,
                    "spaceBetween": 0,
                    "slidesPerColumn": 1,
                    "slidesPerColumnFill": "column",
                    "slidesPerGroup": 1,
                    "centeredSlides": false,
                    "slidesOffsetBefore": 0,
                    "slidesOffsetAfter": 0,
                    "roundLengths": false,
                    "touchRatio": 1,
                    "touchAngle": 45,
                    "simulateTouch": true,
                    "shortSwipes": true,
                    "longSwipes": true,
                    "longSwipesRatio": 0.5,
                    "longSwipesMs": 300,
                    "followFinger": true,
                    "onlyExternal": false,
                    "threshold": 0,
                    "touchMoveStopPropagation": true,
                    "touchReleaseOnEdges": false,
                    "uniqueNavElements": true,
                    "pagination": null,
                    "paginationElement": "span",
                    "paginationClickable": false,
                    "paginationHide": false,
                    "paginationBulletRender": null,
                    "paginationProgressRender": null,
                    "paginationFractionRender": null,
                    "paginationCustomRender": null,
                    "paginationType": "bullets",
                    "nextButton": null,
                    "prevButton": null,
                    "watchSlidesProgress": true,
                    "grabCursor": false,
                    "preventClicks": true,
                    "preventClicksPropagation": true,
                    "slideToClickedSlide": false,
                    "lazyLoading": false,
                    "lazyLoadingInPrevNext": false,
                    "lazyLoadingInPrevNextAmount": 1,
                    "lazyLoadingOnTransitionStart": false,
                    "preloadImages": true,
                    "updateOnImagesReady": true,
                    "loop": false,
                    "loopAdditionalSlides": 0,
                    "loopedSlides": null,
                    "controlInverse": false,
                    "controlBy": "slide",
                    "normalizeSlideIndex": true,
                    "allowSwipeToPrev": true,
                    "allowSwipeToNext": true,
                    "swipeHandler": null,
                    "noSwiping": true,
                    "noSwipingClass": "swiper-no-swiping",
                    "passiveListeners": true,
                    "containerModifierClass": "swiper-container-",
                    "slideClass": "swiper-slide",
                    "slideActiveClass": "swiper-slide-active",
                    "slideDuplicateActiveClass": "swiper-slide-duplicate-active",
                    "slideVisibleClass": "swiper-slide-visible",
                    "slideDuplicateClass": "swiper-slide-duplicate",
                    "slideNextClass": "swiper-slide-next",
                    "slideDuplicateNextClass": "swiper-slide-duplicate-next",
                    "slidePrevClass": "swiper-slide-prev",
                    "slideDuplicatePrevClass": "swiper-slide-duplicate-prev",
                    "wrapperClass": "swiper-wrapper",
                    "bulletClass": "swiper-pagination-bullet",
                    "bulletActiveClass": "swiper-pagination-bullet-active",
                    "buttonDisabledClass": "swiper-button-disabled",
                    "paginationCurrentClass": "swiper-pagination-current",
                    "paginationTotalClass": "swiper-pagination-total",
                    "paginationHiddenClass": "swiper-pagination-hidden",
                    "paginationProgressbarClass": "swiper-pagination-progressbar",
                    "paginationClickableClass": "swiper-pagination-clickable",
                    "paginationModifierClass": "swiper-pagination-",
                    "lazyLoadingClass": "swiper-lazy",
                    "lazyStatusLoadingClass": "swiper-lazy-loading",
                    "lazyStatusLoadedClass": "swiper-lazy-loaded",
                    "lazyPreloaderClass": "swiper-lazy-preloader",
                    "notificationClass": "swiper-notification",
                    "preloaderClass": "preloader",
                    "zoomContainerClass": "swiper-zoom-container",
                    "observer": false,
                    "observeParents": false,
                    "a11y": false,
                    "prevSlideMessage": "Previous slide",
                    "nextSlideMessage": "Next slide",
                    "firstSlideMessage": "This is the first slide",
                    "lastSlideMessage": "This is the last slide",
                    "paginationBulletMessage": "Go to slide {{index}}",
                    "runCallbacksOnInit": true
                });
            }
        }
    }