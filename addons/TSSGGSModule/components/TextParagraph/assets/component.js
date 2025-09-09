var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                elements_: '',
            }
        },
        props: [
            'text',
            'css',
            'elements',
        ],
        created: function () {
        },

    }