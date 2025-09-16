var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'options'
        ],
        data: function () {
            return {
                options_: []
            }
        },
    }