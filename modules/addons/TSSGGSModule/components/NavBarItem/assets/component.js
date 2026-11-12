var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                title_: '',
                url_: '',
                target_: null,
                icon_: '',
                active_: '',
                elements_: '',
                open_: false
            }
        },
        props: [
            'title',
            'url',
            'target',
            'icon',
            'active',
            'elements'
        ],
        mounted: function () {
            // a click anywhere else closes an open dropdown
            var self = this;
            this.closeOutside_ = function (e) {
                if (self.open_ && self.$el && !self.$el.contains(e.target)) {
                    self.open_ = false;
                }
            };
            document.addEventListener('click', this.closeOutside_);
        },
        beforeDestroy: function () {
            document.removeEventListener('click', this.closeOutside_);
        },
        methods: {
            toggle: function (e) {
                if (this.elements_ && this.elements_.elements) {
                    e.preventDefault();
                    this.open_ = !this.open_;
                }
            },
        },
        computed: {
            iconClass: function () {
                return this.icon_ ? 'mdi mdi-' + this.icon_ : ''
            },
            getTarget: function () {
                return this.target_ ? this.target_ : false;
            },
        }
    }