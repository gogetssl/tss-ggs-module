var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin, TooltipMixin],
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                title_: '',
                type_: '',
                outline_: ''
            }
        },
        props: [
            'text',
            'title',
            'type',
            'outline',
        ],
        created: function () {
        },
        computed: {
            badgeType: function () {
                return this.type_ ? 'lu-badge--' + this.type_ : '';
            },
            outlineClass: function () {
                return this.outline_ ? 'lu-badge--outline' : null;
            },
        }
    }