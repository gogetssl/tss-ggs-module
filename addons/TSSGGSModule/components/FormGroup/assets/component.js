var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                elements_: '',
                content_: '',
                error_: '',
                fieldName_: '',
            }
        },
        props: [
            'text',
            'css',
            'elements',
            'content',
            'error',
            'fieldName',
        ],
        created: function () {
            if (!this.fieldName_)
            {
                return;
            }
            
            this.$eventManager().onSetFieldError(this.fieldName_, function (message) {
                this.error_ = message;
                this.$emit('formErrorOccurred', this.cid_, {fieldName: this.fieldName_, message: message})
            }.bind(this));
            
            this.$eventManager().onResetFormErrors(function () {
                this.error_ = '';
                this.$emit('resetFormErrors', this.cid_)
            }.bind(this))
        },

        methods: {},
        computed: {
            formGroupClass: function () {
                return this.css_ + (this.error_ ? ' lu-is-error' : '');
            }
        }
    }