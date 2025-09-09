const mgVuePageControler = function (controlerId)
{
    //main app container id
    this.vueLoaderId = controlerId;
    //main app instance
    this.vueLoader = false;
    
    //main app instance init
    this.vinit = function () {
        var self = this;
        Vue.use(Vuex);
        Vue.use(ActionHandler);
        Vue.use(RequestHandler);
        Vue.use(EventManager);
        Vue.use(AlertManager);
        Vue.use(ResponseValidator);
        self.vueLoader = new Vue(self.getVueAppInits());
    };
    
    //prepare main app config object
    this.getVueAppInits = function () {
        var vAppId = this.vueLoaderId;
        var newVueAppConfig = mgDefauleVueObject;
        
        newVueAppConfig.el = '#' + vAppId;
        newVueAppConfig.data.targetId = vAppId;
        
        //build in AssetsBuilder
        newVueAppConfig.components = vueComponents;
        
        newVueAppConfig.store = new Vuex.Store({
            state: {
                componentsParams: {},
                overlayComponents: [],
                ...vueStoreData
            },
            getters: {
                getOverlayComponents: (state) => {
                    return state.overlayComponents;
                },
                getComponentParams: (state) => (id) => {
                    return state.componentsParams[id] ? state.componentsParams[id] : null;
                }
            },
            mutations: {
                setOverlayComponent(state, payload)
                {
                    state.overlayComponents = [
                        {
                            component: payload.component,
                            parent: payload.parent ? payload.parent : null
                        }
                    ];
                },
                clearOverlayComponent(state)
                {
                    state.overlayComponents = []
                },
                setCompomentParams(state, payload)
                {
                    state.componentsParams[payload.id] = payload.data;
                }
            }
        });
        
        
        return newVueAppConfig;
    }
}
