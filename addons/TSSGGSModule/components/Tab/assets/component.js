var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'classes',
            'content',
            'title',
            'elements',
            'isActive',
        ],
        
        data: function () {
            return {
                elements_: [],
                content_: '',
                isActive_: false,
            };
        },
        computed: {
            paddingClass: function () {
                return '';
            }
        },
        mounted: function () {
            if (this.isActive_)
            {
                this.$emit('setActiveTab', this.cid_)
            }
        },
        methods: {
            onReloadParent: function () {
                this.loadDataFromProps_();
            },
            onFormErrorOccurred: function () {
                this.$emit('setErroredTab', this.cid_)
            },
        }
    }