var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                title_: "",
                url_: "",
                elements_: [],
                active_: false,
            }
        },
        props: [
            'title',
            'url',
            'elements',
            'active'
        ],
        created: function () {
        },
        computed: {
        }
    }