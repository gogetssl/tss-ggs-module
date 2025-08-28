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
                fieldType_: 'password',
            }
        },
        props: [
            'text',
            'css',
            'elements',
            'content'
        ],
        created: function () {
        },

        methods: {
            toggleText: function () {
                this.fieldType_ = this.fieldType_ == 'password' ? 'text' : 'password'
            },
        },
        computed: {
            computedText: function () {
                return this.fieldType_ == 'password' ? '********' : this.text_;
            },
        }
    }