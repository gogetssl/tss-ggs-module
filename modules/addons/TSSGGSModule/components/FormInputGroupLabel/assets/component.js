var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
            }
        },
        props: [
            'text',
        ],
    }