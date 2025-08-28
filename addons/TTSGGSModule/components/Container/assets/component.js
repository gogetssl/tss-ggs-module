var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                elements_: '',
                content_: ''
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

    }