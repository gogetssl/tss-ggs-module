var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                elements_: [],
                columns_: [],
                data_: [],
                title_: '',
                type_: '',
                content_: '',
                actionModal_: '',
                size_: '',
                changelogUrl_: '',
                versions_: [],
                css_: ''
            }
        },
        props: [
            'classes',
            'title',
            'type',
            'fields_values',
            'content',
            'actionModal',
            'size',
            'changelogUrl',
            'versions',
            'css'
        ],
        created()
        {

        },

        methods: {
            closeModal: function () {
                this.$nextTick(() => {
                    this.$actionHandler().modalClose();
                });
            },
        },
        computed: {
            titleClass: function () {
                return this.actionModal_ ?  'lu-text-' + this.type_ : '';
            },
            inTitleClass: function () {
                return this.actionModal_ ? '' : "lu-text-faded lu-font-weight-normal";
            },
            titleIconClass: function () {
                return this.actionModal_  ? (this.type_ == 'danger' ? 'mdi-alert-circle-outline' : 'mdi-information-outline') + ' ' + this.titleClass : '';
            },
            modalClass: function () {
                return this.actionModal_  ? 'lu-modal--info' : '';
            },
            modalSize: function () {
                let sizes = {
                    small: 'lu-modal--sm',
                    large: 'lu-modal--lg',
                    extraLarge: 'lu-modal--xlg',
                    ultraLarge: 'lu-modal--ulg',
                    fullPage: 'lu-modal--full'
                };

                return this.size_ ? sizes[this.size_] : '';
            },
        }
    }