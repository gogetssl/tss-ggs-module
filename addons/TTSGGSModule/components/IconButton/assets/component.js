var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin, TooltipMixin, VisibilityMixin],
        template: '#template-name#',
        props: [
            'data',
            'icon',
            'type',
            'size',
            'classes',
            'title',
            'label',
            'labelPosition',
        ],
        data: function () {
            return {
                data_: null,
                icon_: '',
                type_: '',
                size_: '',
                title_: '',
                label_: "",
                labelPosition_: "",
            };
        },
    
        methods: {
            checkLabelPosition: function (side) {
                return this.labelPosition_ === side;
            }
        },

        computed: {
            typeClass: function () {
                return this.type_ ? 'lu-btn--' + this.type_ : '';
            },
            sizeClass: function () {
                return this.size_ ? 'lu-btn--' + this.size_ : '';
            },
            iconAlignClass: function () {
                return !this.label_ ? 'lu-btn--icon' : '';
            }
        }
    }