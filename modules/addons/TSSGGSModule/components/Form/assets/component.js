var component =
    {
        extends: BaseDataComponent,
        mixins: [EventsListMixin],
        template: '#template-name#',
        data: function () {
            return {
                elements_: [],
                data_: [],
                content: '',
                fieldsValues_: [],
                actions_: [],
                container_: 'form',
                css_: '',
                action_: '',
                method_: '',
                target_: ''
            }
        },
        props: [
            'data',
            'fields_values',
            'actions',
            'container',
            'css',
            'action',
            'method',
            'target'
        ],
        created()
        {
            this.$eventManager().onSubmitFormEvent(this, this.submit, this.cid);
        },
        destroyed()
        {
        
        },
        
        methods: {
            getFormData(input = null)
            {
                const formData = (new FormData(this.$refs['form']));
                
                if (input && input.data && input.data.sourceEntries)
                {
                    Object.entries(input.data.sourceEntries).forEach(function (pair) {
                        formData.append(pair[0], pair[1])
                    });
                }
                
                return formData;
            },
            getFormDataAsObject()
            {
                let formDataObj = {};
                this.getFormData().forEach((value, key) => {
                    if (key.includes('[]'))
                    {
                        split = key.replaceAll('[]', '')
                        
                        var values = formDataObj[split];
                        
                        if (typeof values == "undefined" || values.length === 0)
                        {
                            formDataObj[split] = {0: value};
                        } else
                        {
                            const valuesArrayed = Object.values(values);
                            valuesArrayed.push(value);
                            formDataObj[split] = Object.assign({}, valuesArrayed);
                        }
                    } else
                    {
                        formDataObj[key] = value;
                    }
                });
                
                return formDataObj;
            },
            submit: function (input)
            {
                if (input instanceof SubmitEvent)
                {
                    input.preventDefault();
                }
                
                if (this.action_ && this.method_)
                {
                    this.$refs['form'].submit();
                    return true;
                }
                
                var action = (input && input.data && input.data.customAction != null) ? input.data.customAction : this.ajaxProviderAction_;
                
                if (this.loading_)
                {
                    return;
                }
                
                this.fetchData(action, input)
            },
            onReload: function (data = {}) {
                this.$nextTick(() => {
                    this.propagateSlotsData_(data.slots ? data.slots : []);
                    
                    if (data.component && typeof data.component != "undefined")
                    {
                        this.ajaxData_['reloadedBy'] = data.component.name_;
                    }

                    if (data.data && typeof data.data != "undefined")
                    {
                        this.ajaxData_ = {
                            ...this.ajaxData_,
                            ...data.data
                        };
                    }
                    this.fetchData('reload')
                });
            },
            fetchData: function (action, input = null) {
                this.resetErrors();
                this.loadDataFromServer_({
                    formData: this.getFormData(input),
                    providerAction: action
                }, this.ajaxData_).then((data) => {
                    input && input.resolve ? input.resolve() : null;

                    //form validation error
                    if (typeof data.data == "undefined" || data.data.status !== "success")
                    {
                        return;
                    }

                    if (this.actions_ && this.actions_['onSubmit'])
                    {
                        this.$actionHandler().handle(this.actions_['onSubmit'], this, this.getFormDataAsObject());
                    }
                });
            }
        },
    }