var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin, TooltipMixin, VisibilityMixin],
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                title_: '',
                type_: null,
                leftTextPosition_: false
            }
        },
        props: [
            'text',
            'css',
            'title',
            'type',
            'leftTextPosition',
        ],
        computed: {
            iconType: function () {
                return this.type_ ? 'lu-icon--' + this.type_ : '';
            },
        }
        
    }