var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                title_: "",
                items_: []
            }
        },
        props: [
            'title',
            'items'
        ],
        created: function () {
        },
        computed: {
        }
    }