var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'items',
            'elements'
        ],
        created(){
        },
        data: function () {
            return {
                items_: [],
                css_: [],
                elements_: []
            };
        },
    }