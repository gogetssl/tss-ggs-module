var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        mixins: [FormField, ActionsHandlerMixin],
        props: [
            'type'
        ],
        data: function () {
            return {
                type_: 'text'
            }
        },
    }