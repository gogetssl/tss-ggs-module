var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                elements_: {
                    elements: []
                },
                content_: '',
                name_: ''
            }
        },
        props: [
            'text',
            'css',
            'elements',
            'content',
            'name'
        ],
        created: function () {
        },

        
        methods: {
            change: function (event) {
                this.$emit('change', {
                    'column': this.cid,
                    'event': event
                });
            },
        },
    }