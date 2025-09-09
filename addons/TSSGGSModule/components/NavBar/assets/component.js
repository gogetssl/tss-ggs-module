var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                name_: '',
                logo_url_: '',
                elements_: [],
                paddingClass_: '',
            }
        },
        props: [
            'name',
            'logo_url',
            'elements',
            'paddingClass'
        ],
        created: function () {
        },

    }