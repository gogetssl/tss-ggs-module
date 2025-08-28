var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'value',
            'options',
            'multiple',
            'ajaxSearch',
            'ajaxOnLoad',
            'tagger',
            'setDefaultValueAsFirstOption',
            'itemsPattern',
            'groups',
        ],
        data: function () {
            return {
                options_: [],
                multiple_: '',
                ajaxSearch_: '',
                ajaxOnLoad_: '',
                tagger_: false,
                value_: [],
                selectize_: null,
                setDefaultValueAsFirstOption_: false,
                isCreated_: false,
                itemsPattern_: '',
                groups_: []
            };
        },
        created: function () {

            this.$eventManager().onSetFieldValue(this.name_, function (value) {
                this.setFieldValue(value, true);
            }.bind(this));

            this.$eventManager().onSetFieldValueById(this.cid, function (value) {
                this.setFieldValue(value, true);
            }.bind(this))

            this.$nextTick(function () {
                this.selectize_ = $("#" + this.cid_).selectizeMg({
                    plugins: this.multiple_ ? ['remove_button'] : [],
                    valueField: 'value',
                    labelField: 'name',
                    searchField: ['search'],
                    options: this.options_,
                    create: this.tagger_,
                    preload: this.ajaxOnLoad_,
                    optgroupField: 'group',
                    optgroupLabelField: 'name',
                    optgroupValueField: 'value',
                    optgroups: this.groups_,
                    load: this.ajaxOnLoad_ || this.ajaxSearch_ ? this.loadData : null,
                    wrapperClass: this.tagger_ ? 'lu-selectize-control lu-selectize-tagger' : "lu-selectize-control",
                    items: this.getValues(),
                    onDropdownClose  : () =>  {
                        this.selectize_.clearCache();
                    },
                    onItemAdd: function (item) {
                        this.onItemAdd(item);
                    }.bind(this),
                    onItemRemove: function (item) {
                        this.onItemRemove(item);
                    }.bind(this),
                    onChange: function (value) {
                        this.value_ = value;
                        this.onChange();
                    }.bind(this)
                })[0].selectize;
                
                this.isCreated_ = true;
                
                if (this.readonly_)
                {
                    this.selectize_.lock();
                }
                
            }.bind(this));
        },
        beforeDestroy: function () {
            if (this.selectize_)
            {
                this.selectize_.destroy();
            }
        },
        methods: {
            loadData: function (query, callback) {
                this.selectize_.clearCache();
                this.selectize_.refreshOptions(true);

                this.loadDataFromServer_({
                    query: query
                }, this.ajaxData_).then(function () {
                    callback(this.options_);
                }.bind(this));
            },
            getValues: function () {
                if (this.value_ == '' && this.setDefaultValueAsFirstOption_ && this.options_.length > 0)
                {
                    return [this.options_[0].value];
                }
                
                return Array.isArray(this.value_) ? this.value_ : [this.value_];
            },
            
            getPlaceholderByFieldConfiguration : function () {

                if (this.ajaxSearch_) {
                    return this.translations_['search_placeholder'];
                } else if (this.tagger_) {
                    return this.translations_['tagger_placeholder'];
                }
                
                return this.translations_['static_placeholder'];
            },
            onReload: function (data = {}) {
                this.propagateSlotsData_(data.slots ? data.slots : []);
                this.loadDataFromServer_({}, this.ajaxData_).then(function () {
                    this.selectize_.clearOptions();
                    this.selectize_.addOption(this.options_);
                    this.selectize_.setValue(this.getValues());
                }.bind(this));
            },
            onItemAdd: function (item = null) {

                if (this.itemsPattern_.length > 0 && !(new RegExp(this.itemsPattern_).test(item)))
                {
                    this.selectize_.removeOption(item);
                    return;
                }

                this.$nextTick(function () {
                    this.runAction(null, 'onItemAdd', {addedItem: item});
                }.bind(this));
            },
            onItemRemove: function (item = null) {
                this.$nextTick(function () {
                    this.runAction(null, 'onItemRemove', {removedItem: item});
                }.bind(this));
            },
            setFieldValue: function (value, silent) {
                this.value_ = typeof value == "string" ? value.split(",") : this.value_ = value;
                this.selectize_.setValue(this.getValues(), silent);
            },
        },
        computed: {
            fieldName_: function () {
                return this.multiple_ ? this.name_ + '[]' : this.name_;
            },
            getPlaceholder_: function () {
                return this.placeholder ? this.placeholder : this.getPlaceholderByFieldConfiguration();
            }
        },
    }