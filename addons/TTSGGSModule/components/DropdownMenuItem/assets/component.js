var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'icon',
            'title',
            'type'
        ],
        data: function () {
            return {
                icon_: '',
                title_: '',
                type_: ''
            };
        },
        created: function () {
        },

        methods: {
            // loadData: function(query, callback){
            //     this.loadDataFromServer_({
            //         query: query
            //     }).then(function(){
            //         callback(this.options_);
            //     }.bind(this));
            // },
            // toggleDropdown: function(event){
            //     event.preventDefault();
            //     this.is_drop_open_ = !this.is_drop_open_;
            // },
            // hideDrop: function(event){
            //     event.preventDefault();
            // },
            // notHideDrop: function(event){
            //     event.preventDefault();
            // },
            
        },
        computed: {
            linkClass: function () {
                
                let out = ''
                if (this.type_)
                {
                    out += ' lu-text-' + this.type_ + ' ';
                }
                
                return out;
            },
        }
    }