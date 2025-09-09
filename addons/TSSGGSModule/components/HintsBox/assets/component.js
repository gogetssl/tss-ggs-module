var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'title',
            'elements',
            'css'
        ],
        data: function () {
            return {
                title_: '',
                elements_: [],
                css_: '',
            };
        },
    }