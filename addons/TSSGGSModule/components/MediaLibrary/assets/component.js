var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin, AjaxMixin],
        template: '#template-name#',
        props: [
            'data',
            'text',
            'title',
            'img',
            'elements',
            'autoload_data_after_created',
            'mode',
            'parsedElements',
            'searchedPhrase',
        ],
        data: function () {
            return {
                show_title: true,
                elements_: [],
                parsedElements_: [],
                title_: '',
                img_: '',
                searchedPhrase_: '',
                mode_: false,
            };
        },
        created: function () {
            // console.log(this.elements_);
        },
        methods: {
            reloadParent: function () {
                return this.loadDataFromServer_({ }, this.ajaxData_,).then(typeof this.ajaxLoaded_ === 'function' ? this.ajaxLoaded_ : null);
            },
            isEmptyLibrary: function () {
                return !(this.elements_.elements && this.elements_.elements.length > 0)
            },
            change: function (e) {
                this.$emit('change', e);
            },
            ajaxLoaded_: function () {
                this.$emit('componentLoaded', this.elements_.elements);
                
                this.parsedElements_ = this.elements_.elements;
            },
        },
        watch: {
            searchedPhrase_: {
                deep: false,
                handler: function () {
                    this.searchedPhrase_ = String(this.searchedPhrase_);

                    const searchedPhrase = this.searchedPhrase_.toLocaleLowerCase();

                    this.parsedElements_ = this.elements_.elements.filter((item) => {
                        return  item.slots.imageName.toLowerCase().includes(searchedPhrase)
                    });
                }
            },
        }
    }