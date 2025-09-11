var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin, AjaxMixin],
        template: '#template-name#',
        data: function () {
            return {
                accordionElements_: [],
                type_: '',
                mode_: '',
                css_: '',
                itemsBorder_: false,
            }
        },
        props: [
            'accordionElements',
            'type',
            'mode',
            'css',
            'itemsBorder',
        ],
        created: function () {
        },
        methods: {
            change: function () {
                if (this.mode_ === 'accordion') {
                    this.$children.forEach(function (element) {
                        element.collapseElement()
                    });
                }
            }
        }
    }