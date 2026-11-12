var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'iconSvg',
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
                iconSvg_: '',
                type_: '',
                title_: '',
                size_: '',
                css_: '',
                clickEvent_: '',
                icon_: '',
            };
        },
    }