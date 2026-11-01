var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                items_: [],
                module_: ''
            }
        },
        props: [
            'items',
            'module'
        ],
        computed: {
            // the items come as an array or as an object keyed by position
            list: function () {
                return this.items_ ? Object.values(this.items_) : [];
            }
        }
    }
