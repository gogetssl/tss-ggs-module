var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                items_: []
            }
        },
        props: [
            'items'
        ],
        created: function () {
        },
        computed: {
            moduleUrlClassLogo: function () {
                return 'lu-product-{$tagImageModule}-for-whmcs'
            }
            
        }
    }