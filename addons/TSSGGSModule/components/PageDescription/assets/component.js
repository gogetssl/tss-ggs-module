var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                elements_: '',
                content_: '',
                img_: '',
                title_: '',
                description_: ''
            }
        },
        props: [
            'text',
            'css',
            'elements',
            'content',
            'img',
            'title',
            'description'
        ],
    }