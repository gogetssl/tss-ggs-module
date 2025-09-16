var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                title_: '',
                type_: '',
                elements_: [],
                removeIcon_: false,
                textCentered_: false,
                isOpen_: false
            }
        },
        props: [
            'text',
            'title',
            'type',
            'elements',
            'cid',
            'removeIcon',
            'textCentered',
        ],
        created: function () {
        },
        methods: {
            change: function () {
                this.$emit('change')

                this.isOpen_ = !$(this.targetId).hasClass('lu-show');
            },
            collapseElement: function () {
                $(this.targetId).collapse('hide');
            },
        },
        computed: {
            targetId: function () {
                return '#element-' + this.cid_;
            },
            textCenterClass: function () {
                return this.textCentered_ ? 'lu-text-center' : ''
            },
        }
    }