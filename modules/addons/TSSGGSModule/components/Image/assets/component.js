var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                url_: '',
            }
        },
        props: [
            'text',
            'css',
            'url'
        ],
        created: function () {
        },

    }