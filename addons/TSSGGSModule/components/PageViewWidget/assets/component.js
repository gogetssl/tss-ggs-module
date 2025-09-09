var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'title',
            'image',
            'details',
            'buttonsContainer',
            'elements',
        ],
        data: function () {
            return {
                css_: [],
                title_: '',
                image_: null,
                details_: null,
                buttonsContainer_: null,
                elements_: [],
            };
        },
    }