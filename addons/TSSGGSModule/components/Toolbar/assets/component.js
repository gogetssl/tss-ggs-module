var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'elements'
        ],
        data: function () {
            return {
                css_: [],
                elements_: []
            };
        },
        created: function () {
        },

    }