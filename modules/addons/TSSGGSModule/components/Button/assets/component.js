var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'type',
            'title',
            'size',
            'css',
            'clickEvent',
            'click',
            'icon'
        ],
        data: function () {
            return {
                type_: '',
                title_: '',
                size_: '',
                css_: '',
                clickEvent_: '',
                icon_: '',
            };
        },
    }