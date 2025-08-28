var component =
    {
        extends: BaseDataComponent,
        mixins: [Data, ActionsHandlerMixin, EventsListMixin],
        template: '#template-name#',
        data: function () {
            return {
                elements_: [],
                name_: '',
                css_: '',
                container_: 'form',
                providerValidateAction_: '',
                activeTabIndex_: 0,
                isLastStep_: false
            }
        },
        props: [
            'name',
            'css',
            'container',
            'providerValidateAction',
        ],
        created: function () {
            this.$eventManager().onSubmitFormEvent(this, this.submit, this.cid);
        },
        methods: {
            nextStepClick: function (input) {
                if (this.elements_.elements && this.elements_.elements.length > this.activeTabIndex_ + 1)
                {
                    this.setActiveTabIndex(this.activeTabIndex_ + 1, input);
                }
            },
            previousStepClick: function () {
                if (this.activeTabIndex_ > 0)
                {
                    this.setActiveTabIndex(this.activeTabIndex_ - 1);
                }
            },
            setActiveTabIndex: function (index, input) {
                if (index > this.activeTabIndex_ )
                {
                    this.submit(input, this.activeTabIndex_ + 1).then((data) => {
                        if (data.data && data.data.status === 'success')
                        {
                            this.activeTabIndex_ = index;
                            this.isLastStep_ = this.activeTabIndex_ + 1 === this.elements_.elements.length;

                            this.onReloadParent();
                        }
                    });
                } else {
                    this.activeTabIndex_ = index;
                    this.isLastStep_ = this.activeTabIndex_ + 1 === this.elements_.elements.length;
                }
            },
            getFormData(action)
            {
                var formData = (new FormData(this.$refs['form']));

                if (this.isLastStep_)
                {
                    return formData;
                }

                const namesForRemove = [];

                for (const pair of formData.entries())
                {
                    if (action != "reload" && !this.getActiveTabInputsNames().includes(pair[0]))
                    {
                        namesForRemove.push(pair[0])
                    }
                }

                for (const nameForRemove of namesForRemove)
                {
                    formData.delete(nameForRemove);
                }

                return formData;
            },

            getActiveTabInputsNames()
            {
                const activeTabCid = this.elements_.elements[this.activeTabIndex_].cid;
                const activeTab = $("#tab-" + activeTabCid);
                const names = [];

                if (typeof activeTab !== "undefined")
                {
                    var activeTabForm = new FormData($("<form></form>").append(activeTab.clone())[0]);

                    for (const pair of activeTabForm.entries())
                    {
                        names.push(pair[0]);
                    }
                }

                return names;
            },
            getFormDataAsObject()
            {
                let formDataObj = {};
                this.getFormData().forEach( (value, key) => {
                    if (key.includes('[]')) {
                        split = key.replaceAll('[]', '')

                        var values = formDataObj[split];

                        if (typeof values == "undefined" || values.length === 0) {
                            formDataObj[split] = {0:value};
                        } else {
                            valuesArrayed = Object.values(values);
                            valuesArrayed.push(value);
                            formDataObj[split] = Object.assign({}, valuesArrayed);
                        }
                    } else {
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

                var action = (input && input.data && input.data.customAction != null) ? input.data.customAction : this.ajaxProviderAction_;

                if (this.loading_) {
                    return;
                }

                if (!this.isLastStep_)
                {
                    action = this.providerValidateAction_;
                }

                if (this.actions_['onSubmit'])
                {
                    return this.$actionHandler().handle(this.actions_['onSubmit'], this, this.getFormDataAsObject());
                }

                if (this.actions_['onNextStep'])
                {
                    this.$actionHandler().handle(this.actions_['onNextStep'], this, this.getFormDataAsObject());
                }

                return this.fetchData(action, input);
            },
            onReload: function (data = {}) {
                this.propagateSlotsData_(data.slots ? data.slots : []);
                if (data.component && typeof data.component != "undefined")
                {
                    this.ajaxData_['reloadedBy'] = data.component.name_;
                }
                this.fetchData('reload')
            },
            onReloadParent: function (data = {}) {
                this.propagateSlotsData_(data.slots ? data.slots : []);
                if (data.component && typeof data.component != "undefined")
                {
                    this.ajaxData_['reloadedBy'] = data.component.name_;
                }
                this.fetchData('reload')
            },
            fetchData: function (action, input = null) {
                this.resetErrors();
                return this.loadDataFromServer_({
                    formData: this.getFormData(action),
                    providerAction: action,
                    step: this.activeTabIndex_
                }, this.ajaxData_).then(function (data) {
                    input && input.resolve ? input.resolve() : null;
                    return data;
                });
            }
        }

    }