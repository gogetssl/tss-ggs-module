var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin, ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'css',
            'content',
            'title',
            'elements',
            'isOpen',
            'isActive',
            'openOnActiveItems',
            'closeAllProp',
            'deactivateAllProp',
        ],
        data: function () {
            return {
                elements_: [],
                css_: [],
                title_: '',
                content_: '',
                isOpen_: false,
                isActive_: false,
                openOnActiveItems_: false,
                closeAllProp_: false,
                deactivateAllProp_: false,
            };
        },
        created: function () {
            if (this.openOnActiveItems_ && this.isActive_)
            {
                this.openItem();
            }
        },
        methods: {
            openItem: function () {
                this.isOpen_ = true;
                this.$emit('openItem')
            },
            closeAll: function (payload) {
                this.$emit('closeAll', payload)
            },
            deactivateAll: function (payload) {
                this.$emit('deactivateAll', payload)
            },
            closeItem: function () {
                this.isOpen_ = false;
            },
            click: function (data) {

                if (this.isActive_)
                {
                    this.toggle();
                    return;
                }

                this.$emit('deactivateAll', {
                    emitCallback:  function() {
                        this.isActive_ = true;
                    }.bind(this)
                });
                
                this.$emit('closeAll', {
                    emitCallback:  function() {
                        this.openItem();
                    }.bind(this)
                });
                
                this.onClick();
            },
            toggle: function () {
                this.isOpen_ = !this.isOpen_;
                if (this.ajaxOnAction_ && this.isOpen_)
                {
                    this.runAjaxOnAction_();
                }
            },
        },
        computed: {
            openClass_: function () {
                return this.isOpen_ ? 'is-open' : '';
            },
            activeClass_: function () {
                return this.isActive_ ? 'is-active' : '';
            },
        },
        watch: {
            closeAllProp_(val) {
                this.isOpen_ = false;
            },
            deactivateAllProp_(val) {
                this.isActive_ = false;
            }
        }
    }