var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        mixins: [FormField, ActionsHandlerMixin],
        props: [
            'value',
            'options',
            'setDefaultValueAsFirstOption',
        ],
        data: function () {
            return {
                value_: "",
                options_: [],
                setDefaultValueAsFirstOption_: false,
            }
        },
        created: function () {
            //for WHMCS radio inputs
            this.$nextTick(function() {
                $('#' + this.cid_ + ' input:radio[name=' + this.name_ + ']').on('ifChanged', (event) => {
                    this.checkOption(event);
                });
            }.bind(this));
        },
        methods: {
            isChecked: function(value, index)
            {
                return this.value_ === "" ? this.setDefaultValueAsFirstOption_ && index === 0 : this.value_ === value ;
            },
            checkOption: function(event)
            {
                var target = $(event.target.parentElement);
                
                if (target.length <= 0)
                {
                    return;
                }
                
                var input = target.find('input');
                
                if (input.length > 0 && input.val() != this.value_)
                {
                    input.prop("checked", true).click().trigger('click').trigger('change');
                    
                    //for WHMCS radio inputs
                    
                    var inputsInRadio = $('#' + this.cid_ + ' input:radio[name="' + this.name_  + '"]');
                    
                    if (inputsInRadio.length > 0 && typeof inputsInRadio.iCheck === 'function')
                    {
                        inputsInRadio.iCheck('update')
                    }
    
                    this.value_ = input.val();
                    
                    this.onChange(event);
                }
            },
        }
    }