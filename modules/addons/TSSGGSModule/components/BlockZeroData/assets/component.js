var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'iconSvg',
            'css',
            'description',
            'additionalDescription',
            'title',
            'elements',
            'icon'
        ],
        data: function () {
            return {
                iconSvg_: '',
                elements_: [],
                description_: '',
                additionalDescription_: '',
                title_: '',
                css_: [],
                icon_: '',
            };
        },
        created: function () {
        
        },
        
    }