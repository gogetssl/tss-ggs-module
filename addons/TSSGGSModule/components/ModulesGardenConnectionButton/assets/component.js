var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'css',
            'clickEvent',
            'click',
            'url',
            'text'
        ],
        data: function () {
            return {
                css_: '',
                clickEvent_: '',
                url_: '',
                text_: ''
            };
        },
    }