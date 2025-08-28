var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'description',
            'additionalDescription',
            'title',
            'elements',
            'icon'
        ],
        data: function () {
            return {
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