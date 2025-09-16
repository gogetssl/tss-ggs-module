var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                icon_: '',
                for_: '',
                css_: ''
            }
        },
        props: [
            'text',
            'icon',
            'for',
            'css',
        ],
    }