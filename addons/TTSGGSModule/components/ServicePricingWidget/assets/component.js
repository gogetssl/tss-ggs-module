var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'css',
            'title',
            'elements',
            'dataSet',
            'enterPriceCallback',
        ],
        data: function () {
            return {
                css_: "",
                title_: '',
                elements_: [],
                dataSet_: [],
                enterPriceCallback_: '',
            };
        },
        created: function () {
            if (this.enterPriceCallback_.length > 0)
            {
                this.enterPriceCallbackFn = Function('data', this.enterPriceCallback_);
            }

            this.$nextTick(function () {
                $("#" + this.cid_ + " select.lu-form-control").each((item, value)  => {
                    const dataKey = $(value).attr('data-dropdown-key');
                    const dataRow =  this.dataSet_[dataKey];

                    if (typeof dataRow == "undefined" || typeof dataRow.dropdownInput == "undefined")
                    {
                        return;
                    }

                    const dropdownValue = dataRow.dropdownInput ? dataRow.dropdownInput.value : [];
                    const dropdownOptions = dataRow.dropdownInput ? dataRow.dropdownInput.options : [];

                    $(value).selectizeMg({
                        valueField: 'value',
                        labelField: 'name',
                        options: dropdownOptions,
                        wrapperClass: "lu-selectize-control",
                        items: [dropdownValue],
                        onChange: (value) => {
                            this.changeDropdownValue(value, dataKey);
                        }
                    });
                });

                $("#" + this.cid_ + " .lu-tooltip").luTooltip({});

            }.bind(this));
        },
        methods: {
            changeDropdownValue: function (value = null, key) {
                let dataRow = this.dataSet_[key];

                if (typeof dataRow == "undefined" || typeof dataRow.dropdownInput == "undefined")
                {
                    return;
                }

                dataRow.dropdownInput.value = value;

                return this.dataSet_[key] = this.enterPriceCallbackFn(dataRow);
            },
            changeInputValue: function (event = null, key) {
                let dataRow = this.dataSet_[key];

                if (typeof dataRow == "undefined" || typeof dataRow.priceInput == "undefined")
                {
                    return;
                }

                dataRow.priceInput.value = $(event.target).val();

                return this.dataSet_[key] = this.enterPriceCallbackFn(dataRow);
            },

            enterPriceCallbackFn: function (data) {
                return data;
            }
        },
        computed: {

        }
    }