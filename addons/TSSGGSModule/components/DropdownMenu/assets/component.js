var component =
    {
        extends: BaseDataComponent,
        mixins: [TooltipMixin],
        template: '#template-name#',
        props: [
            'autoload_data_after_created',
            'start_length',
            'value',
            'options',
            'multiple',
            'ajaxSearch',
            'ajaxOnLoad',
            'elements',
            'title',
        ],
        data: function () {
            return {
                options_: [],
                multiple_: '',
                ajaxSearch_: '',
                ajaxOnLoad_: '',
                title_: '',
                is_drop_open_: false,
                elements_: []
            };
        },
        created: function () {
            window.addEventListener('click', this.hideWhenClickOutside);
            
            this.title_ = this.title_ ? this.title_ : this.translate_('more_actions');
        },
        destroyed: function () {
            window.removeEventListener('click', this.hideWhenClickOutside);
        },
        
        methods: {
            loadData: function (query, callback) {
                this.loadDataFromServer_({
                    query: query
                }, this.ajaxData_).then(function () {
                    callback(this.options_);
                    this.toggleDropdown(null)
                }.bind(this));
            },
            toggleDropdown: function (event) {
                event ? event.preventDefault() && event.stopPropagation() : null;
                this.is_drop_open_ = !this.is_drop_open_;
            },
            hideDrop: function (event) {
                event.preventDefault();
            },
            notHideDrop: function (event) {
                event.preventDefault();
            },
            hideWhenClickOutside: function (event) {
                if (this.is_drop_open_ && !this.$el.contains(event.target))
                {
                    this.is_drop_open_ = false;
                }
            }
        }
    }