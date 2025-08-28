var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        mixins: [FormField],
        data: function () {
            return {
                multiple_: false,
                accept_: "",
                placeholder_: "",
                hasSelectedFiles_: false,
                selectedFiles_: [],
            }
        },
        props: [
            'multiple',
            'accept',
            'placeholder'
        ],
        methods: {
            change: function (event) {
                this.hasSelectedFiles_ = false;
                this.selectedFiles_ = [];

                const files = event.target.files;

                if (typeof files != "undefined" && files.length > 0)
                {
                    this.selectedFiles_ = files;
                    this.hasSelectedFiles_ = true;
                }
            },
            triggerInput: function () {
                this.$el.getElementsByTagName('input')[0].click()
            },
            getJoinedNames: function () {
                return Array.from(this.selectedFiles_).map(file => {
                    return file.name;
                }).join(', ');
            },
        },
        computed: {
            placeholderContent: function () {
                return this.hasSelectedFiles_ ? this.getJoinedNames() : this.translate_(this.placeholder_);
            },
        }
    }