var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'columns',
            'records',
            'textCentered',
            'css'
        ],
        data: function () {
            return {
                columns_: [],
                records_: [],
                textCentered_: false,
                css_: ''
            };
        },
        created()
        {
        },
        computed: {
            textCenterClass: function () {
                return this.textCentered_ ? 'lu-text-center' : ''
            },
        }
    }