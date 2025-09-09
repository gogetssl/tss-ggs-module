var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'itemId',
            'date',
            'title',
            'description',
            'read'
        ],
        data: function () {
            return {
                itemId_: '',
                date_: '',
                title_: '',
                description_: '',
                read_: false
            };
        },
        created: function () {
    
        },

        methods: {
            deleteItem: function (event) {
                this.$emit('deleteItem', { 'itemId' : this.itemId_})
            },
        },
        computed: {
            readClass: function () {
                return this.read ? '' : 'lu-bg-secondary-faded';
            },
        }
    }