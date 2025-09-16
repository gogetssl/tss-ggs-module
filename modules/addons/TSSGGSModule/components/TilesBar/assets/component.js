var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'title',
            'elements',
            'titleTextCentered',
        ],
        data: function () {
            return {
                title_: '',
                elements_: [],
                titleTextCentered_: false,
            };
        },
        computed: {
            titleCenteredClass: function () {
                return this.titleTextCentered_ ? 'text-center' : ''
            }
        }
    }