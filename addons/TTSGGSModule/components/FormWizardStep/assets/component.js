var component =
    {
        extends: BaseDataComponent,
        mixins: [Data],
        template: '#template-name#',
        data: function () {
            return {
                elements_: [],
                data_: [],
                content: '',
                fieldsValues_: [],
                actions_: [],
                container_: 'div',
                css_: '',
            }
        },
        props: [
            'data',
            'fields_values',
            'elements',
            'actions',
            'container',
            'css',
        ],
        created: function () {
        },

    }