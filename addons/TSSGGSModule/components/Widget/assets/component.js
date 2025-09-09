var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'content',
            'title',
            'elements',
            'icon',
            'customcontentclass'
        ],
        data: function () {
            return {
                elements_: [],
                css_: [],
                title_: '',
                content_: '',
                icon_: '',
                customcontentclass_: ''
            };
        },
        created: function () {
        },

        computed: {
        }
    }