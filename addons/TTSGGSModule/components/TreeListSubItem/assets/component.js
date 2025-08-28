var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                css_: '',
                elements_: '',
                title_: '',
            }
        },
        props: [
            'css',
            'elements',
            'title',
        ],
        created: function () {
        },

    }