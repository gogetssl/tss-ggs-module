var component =
    {
        extends: BaseDataComponent,
        mixins: [TooltipMixin],
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                title_: '',
                iconSvg_: ''
            }
        },
        props: [
            'text',
            'css',
            'title',
            'iconSvg'
        ],
        created: function () {
        },

    }