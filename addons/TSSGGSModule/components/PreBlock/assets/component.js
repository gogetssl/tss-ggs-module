var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'classes',
            'content',
        ],
        data: function () {
            return {
                content_: ''
            };
        },
    }