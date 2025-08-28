var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'items',
        ],
        created: function(){
        },
        
        data: function () {
            return {
                items_: [],
                css_: [],
            };
        },
    }