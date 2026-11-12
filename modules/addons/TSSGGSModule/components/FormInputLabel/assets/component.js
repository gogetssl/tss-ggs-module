var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                iconSvg_: '',
                text_: '',
                icon_: '',
                for_: '',
                css_: ''
            }
        },
        props: [
            'iconSvg',
            'text',
            'icon',
            'for',
            'css',
        ],
    }