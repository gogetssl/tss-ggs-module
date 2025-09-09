var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                css_: '',
                elements_: '',
            }
        },
        props: [
            'css',
            'elements',
        ],
        created: function () {
        },

    }