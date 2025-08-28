var component =
    {
        extends: BaseDataComponent,
        mixins: [Data, ActionsHandlerMixin],
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
                size_: ''
            }
        },
        props: [
            'classes',
            'title',
            'type',
            'fields_values',
            'content',
            'actionModal',
            'size'
        ],
        created()
        {
            this.data_ = this.$attrs.parent.data_;
            this.$eventManager().onModalCloseEvent(this, this.onCloseModalEvent, this.cid);
        },

        methods: {
            closeModal: function () {
                this.$nextTick(() => {
                    this.$actionHandler().modalClose({elementId: this.cid_}, this);
                });
            },
            onCloseModalEvent: function () {
                this.onClose();
            },
        },
        computed: {
            titleClass: function () {
                return this.actionModal_ ?  'lu-text-' + this.type_ + ' ' + 'lu-type-4' : 'lu-type-6';
            },
            inTitleClass: function () {
                return this.actionModal_ ? '' : "lu-text-faded lu-font-weight-normal";
            },
            titleIconClass: function () {
                return this.actionModal_  ? (this.type_ == 'danger' ? 'mdi-alert-circle-outline' : 'mdi-information-outline') + ' ' + 'lu-text-' + this.type_ : '';
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