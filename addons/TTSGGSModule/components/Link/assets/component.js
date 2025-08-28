var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                url_:'',
                title_:''
            }
        },
        props: [
            'text',
            'css',
            'url',
            'title'
        ],
        created: function () {
        },

    }