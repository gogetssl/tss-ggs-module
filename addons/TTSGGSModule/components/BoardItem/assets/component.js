var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                css_: '',
                styles_: [],
                elements_: [],
                topElements_: [],
                title_: '',
                subTitle_: '',
                text_: '',
            }
        },
        props: [
            'css',
            'styles',
            'elements',
            'topElements',
            'title',
            'subTitle',
            'text',
        ],
        created: function () {
        },

        
        methods: {

        },
    }