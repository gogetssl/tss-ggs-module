var component =
    {
        extends: BaseDataComponent,
        mixins: [TooltipMixin],
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                title_: ''
            }
        },
        props: [
            'text',
            'css',
            'title'
        ],

    }