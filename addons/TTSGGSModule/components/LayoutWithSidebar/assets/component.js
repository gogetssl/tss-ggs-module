var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                css_: '',
                elements_: '',
                sidebars_: []
            }
        },
        props: [
            'css',
            'elements',
            'sidebars'
        ],
        created: function () {
        },
        
    }