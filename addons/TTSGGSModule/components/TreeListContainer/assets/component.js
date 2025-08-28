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
            'openOnActiveItems'
        ],
        data: function () {
            return {
                elements_: [],
                css_: [],
                title_: '',
                content_: '',
                openOnActiveItems_: false,
                closeAllProp_: false,
                deactivateAllProp_: false,
            };
        },
        methods: {
            closeAll: function (payload) {
                this.closeAllProp_ = !this.closeAllProp_
                
                if (typeof payload.emitCallback == "function")
                {
                    this.$nextTick(function() {
                        payload.emitCallback.call();
                    }.bind(this));
                }
            },
    
            deactivateAll: function (payload) {
                this.deactivateAllProp_ = !this.deactivateAllProp_;
    
                if (typeof payload.emitCallback == "function")
                {
                    this.$nextTick(function() {
                        payload.emitCallback.call();
                    }.bind(this));
                }
            }
        }
    }