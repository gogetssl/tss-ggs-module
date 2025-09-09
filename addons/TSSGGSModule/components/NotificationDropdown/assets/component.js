var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin, AjaxMixin],
        template: '#template-name#',
        props: [
            'options',
            'multiple',
            'ajaxSearch',
            'ajaxOnLoad',
            'elements',
            'unreadCount',
        ],
        data: function () {
            return {
                options_: [],
                multiple_: '',
                ajaxSearch_: '',
                ajaxOnLoad_: '',
                is_drop_open_: false,
                elements_: [],
                unreadCount_: 0,
            };
        },
        created: function () {
            window.addEventListener('click', this.hideWhenClickOutside);
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
            },
            reloadParent: function (event) {
                return this.loadDataFromServer_({
                    "action": "clickItem",
                    "itemId": event.component.itemId_,
                }, this.ajaxData_,).then(typeof this.ajaxLoaded_ === 'function' ? this.ajaxLoaded_ : null);
            },
            deleteItem: function (data) {
                return this.loadDataFromServer_({
                    "action": "deleteItem",
                    "itemId": data.itemId,
                }, this.ajaxData_,).then(typeof this.ajaxLoaded_ === 'function' ? this.ajaxLoaded_ : null);
            },
        },
    }