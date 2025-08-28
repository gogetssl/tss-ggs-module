var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'css',
            'text',
            'type',
            'outline',
            'dismiss_button',
            'size',
            'title',
        ],
        data: function () {
            return {
                css_: [],
                text_: '',
                type_: '',
                outline_: '',
                dismiss_button_: '',
                visible_: true,
                size_: '',
                title_: '',
            };
        },
        computed: {
            typeClass: function () {
                return 'lu-alert--' + this.type_;
            },
            outlineClass: function () {
                return this.outline_ ? 'lu-alert--outline' : null;
            },
            sizeClass: function () {
                return this.size_ ? 'lu-alert--' + this.size_ : null;
            },
        },
        created()
        {
        },
    }