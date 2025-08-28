var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin, ComponentsContainer, TooltipMixin],
        template: '#template-name#',
        props: [
            'css',
            'elements',
            'title',
            'description',
            'content',
        ],
        data: function () {
            return {
                css_: [],
                elements_: [],
                title_: "",
                description_: "",
                content_: '',
            };
        },
        computed: {
        },
        created()
        {
        },
    }