var component =
    {
        components: vueComponents,
        template: '#template-name#',
        props: [
            'element',
            'autoHideFieldName',
            'autoHideFieldValue',
            'autoHideOperator',
            'autoDisableFieldName',
            'autoDisableFieldValue',
            'autoDisableOperator',
            'cid'
        ],
        data: function () {
            return {
                operators: {
                    '==': function(a, b) { return a == b },
                    '!=': function(a, b) { return a != b }
                }
            }
        },
        methods: {
            checkHidden: function () {
                return this.autoHideFieldName &&
                    typeof this.$attrs.data != 'undefined' &&
                    this.compareValues(this.$attrs.data[this.autoHideFieldName], this.autoHideFieldValue, this.autoHideOperator);
            },
            
            checkDisabled: function () {
                return this.autoDisableFieldName &&
                    typeof this.$attrs.data != 'undefined' &&
                    this.compareValues(this.$attrs.data[this.autoDisableFieldName], this.autoDisableFieldValue, this.autoDisableOperator);
            },
            
            disabledClass: function () {
                return this.checkDisabled() ? 'disabled' : "";
            },
            
            hiddenClass: function () {
                return this.checkHidden() ? 'hidden' : "";
            },

            compareValues: function (baseValue, valueToCompare, operator = "==") {
                if (typeof this.operators[operator] == "undefined")
                {
                    console.error("Unsupported operator: " + operator);

                    return false;
                }

                return this.operators[operator](baseValue, valueToCompare);
            },
        },
        computed: {
            params_: function () {
                let element = this.element;
                element.slots['class'] = this.disabledClass() + " " + this.hiddenClass();

                let finalActions = this.clearActions ? {} : element.slots['actions'];
                this.clearActions = false;

                return {
                    ...element.slots,
                    ...this.$attrs,
                    ...{
                        actions: finalActions,
                    },
                };
            },
            cid_: function() {
                return this.cid;
            }
        }
    }