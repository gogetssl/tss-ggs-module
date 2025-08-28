var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ActionsHandlerMixin, Data, AjaxMixin, ComponentsContainer],
        template: '#template-name#',
        props: [
            'onOffMode',
        ],
        data: function () {
            return {
                onOffMode_: false,
            };
        },
        created: function () {
            this.value_ = this.onOffMode_ ? ( this.value_ === "on" ) : this.value_;
        },
        methods: {
            change: function (event) {
                this.emitEvents(event);
                this.onChange()
                
                if (this.ajaxOnAction_)
                {
                    this.runAjaxOnAction_({
                        value: this.onOffMode_ ? ( this.value_ ? "on" : "off" ) : this.value_
                    });
                }
            },
            click: function () {
                this.value_ = !this.value_;
            },
            normaliseValue: function (value) {
                return this.onOffMode_ ? ( value ? "on" : "off" ) : (value ? "1" : "0" ) ;
            },
        },
    }