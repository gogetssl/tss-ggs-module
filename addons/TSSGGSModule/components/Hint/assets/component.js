var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'title',
            'description',
            'type',
            'icon',
            'elements',
        ],
        data: function () {
            return {
                title_: '',
                description_: '',
                type_: '',
                icon_: '',
                elements_: [],
            };
        },
        
        computed: {
            typeClass: function () {
                return  'lu-hint--' + this.type_;
            },
            typeIcon: function () {
                return 'mdi-' + this.icon_;
            },
        }
    }