var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'elements'
        ],
        created(){
        },
        data: function () {
            return {
                css_: [],
                elements_: []
            };
        },
    }