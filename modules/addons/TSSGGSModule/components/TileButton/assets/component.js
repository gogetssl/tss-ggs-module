var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'data',
            'text',
            'title',
            'img',
            'active'
        ],
        data: function () {
            return {
                show_title: true,
                data_: null,
                title_: '',
                img_: '',
                active_: '',
            };
        },
        
    }