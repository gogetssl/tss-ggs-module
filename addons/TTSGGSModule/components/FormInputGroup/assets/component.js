var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                elements_: [],
                name_: '',
                value_: '',
                text_: '',
                classes_: '',
                css_: ''
            }
        },
        props: [
            'name',
            'value',
            'text',
            'classes',
            'css'
        ],
        created: function () {

        },

    }