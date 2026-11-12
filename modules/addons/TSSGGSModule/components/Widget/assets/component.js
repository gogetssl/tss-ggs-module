var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'content',
            'title',
            'subtitle',
            'elements',
            'icon',
            'customcontentclass'
        ],
        data: function () {
            return {
                elements_: [],
                css_: [],
                title_: '',
                subtitle_: '',
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