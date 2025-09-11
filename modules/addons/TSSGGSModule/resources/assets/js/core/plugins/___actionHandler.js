const ActionHandler =
    {
        install(Vue, options)
        {
            let self = this;
            Vue.prototype.$actionHandler = function () {
                return self.installVue(this);
            }
        },
        
        installVue(vue)
        {
            this.vue = vue;
            return this;
        },
        
        handle(actions, component, data = {})
        {
            return new Promise(function (resolve, reject) {
                actions.forEach(function (action) {
                    let ret = this[action.action](action, component, data);
                    Promise.resolve(ret).then(function (value) {
                        resolve();
                    })
                }.bind(this));
            }.bind(this));
        },
        
        emit(action, component, data = {})
        {
            this.vue.$emit(action.event, {
                component: component,
                data: data
            });
        },
        
        modalOpen(action, component, data = {})
        {
            this.vue.$store.commit("setOverlayComponent", {
                component: action.modal,
                parent: component
            });
            
            if (component && component.data_)
            {
                this.vue.$nextTick(function () {
                    for (let fieldName in component.data_)
                    {
                        this.vue.$eventManager().setFieldValue(fieldName, component.data_[fieldName]);
                    }
                }.bind(this));
            }
        },
        
        modalLoad(action, component)
        {
            return this.vue.$request().post({
                loadData: action.modal.slots.cid,
                namespace: action.modal.slots.namespace,
                cid: action.modal.slots.cid,
                ...extraParams,
                formData: {
                    ...component.data,
                    ...(action.params ? action.params : null)
                },
                actionElementId: component.data && component.data.id ? component.data.id : null
            }).then(function (data) {
                this.vue.$responseValidator().validate(data.data);
                this.vue.$actionHandler().handle(data.data.actions ? data.data.actions : []);
                this.vue.$store.commit("setOverlayComponent", {
                    component: data.data.data,
                    parent: component
                });
            }.bind(this));
        },
        
        modalClose(action, component)
        {
            this.vue.$store.commit("clearOverlayComponent");
            
            this.vue.$root.$emit('close-modal-' + action.elementId, {
                component: component
            });
        },
        
        modalFormSubmit(action, component)
        {
            let obj = component;
            
            while (obj)
            {
                if (obj.$refs['form'])
                {
                    return this.formSubmit({
                        elementId: obj.$refs['form'].cid
                    }, component);
                }
                obj = obj.$parent;
            }
        },
        
        downloadFile(action, component)
        {
            let params = {
                loadData: action.form.slots.cid,
                namespace: action.form.slots.namespace,
                cid: action.form.slots.cid,
                ajax: true,
                formData: {
                    ...(action.params ? action.params : null)
                },
                actionElementId: component && component.data && component.data.id ? component.data.id : null
            };
            
            let formData = new FormData();
            this.vue.$request().buildFormData(formData, params);
            
            window.location.href = window.location.href + "&" + new URLSearchParams(formData).toString();
        },
        
        setFieldValue(action, component, data = {})
        {
            this.vue.$eventManager().setFieldValue(action.name, action.value);
        },

        setFieldValueById(action, component, data = {})
        {
            document.getElementById(action.id).value = action.value;
        },
        
        formSubmit(action, component, data = {})
        {
            if (action && typeof action.sourceFormSelector != "undefined")
            {
                const sourceForm = $(action.sourceFormSelector)[0];
                let formData = new FormData(sourceForm);
                
                let sourceEntries = {};
                
                for (const pair of formData.entries())
                {
                    sourceEntries[pair[0]] = pair[1];
                }
                
                data.sourceEntries = sourceEntries;
            }
            
            data.customAction = action.customAction;
            
            return new Promise(function (resolve, reject) {
                //@todo use $eventManager
                this.vue.$root.$emit('submit-form-' + action.elementId, {
                    resolve: resolve,
                    reject: reject,
                    component: component,
                    data: data
                });
                
            }.bind(this));
        },
        
        reload(action, component, data = {})
        {
            if (action.slots && typeof action.slots.withDataFromFormId != "undefined")
            {
                const form = $("#" + action.slots.withDataFromFormId);
                
                if (form.length > 0)
                {
                    try {

                        let duplicatedFormData = {};

                        const formData = new FormData(form[0]);

                        if (action.slots.ajaxData === undefined)
                        {
                            action.slots.ajaxData = {};
                        }

                        for (const pair of formData.entries())
                        {
                            if (typeof pair[0] === "string" && pair[0].endsWith('[]'))
                            {
                                let multipleInputKey = pair[0].replace('[]', "");

                                if (typeof duplicatedFormData[multipleInputKey] === "undefined")
                                {
                                    duplicatedFormData[multipleInputKey] = [];
                                }

                                duplicatedFormData[multipleInputKey].push(pair[1]);

                                continue;
                            }

                            duplicatedFormData[pair[0]] = pair[1];
                        }

                        action.slots.ajaxData = {...action.slots.ajaxData, ...duplicatedFormData};

                    } catch (error) {
                        console.error(error);
                    }
                }
            }

            if (action.slots.ajaxData && component && component.data)
            {
                Object.entries(action.slots.ajaxData).forEach(function (entry) {
                    const [key, value] = entry;

                    if (typeof value !== 'string' || typeof component.data[value.substring(1)] === 'undefined')
                    {
                        return;
                    }

                    action.slots.ajaxData[key] = component.data[value.substring(1)].toString();
                }.bind(this));
            }

            return new Promise(function (resolve, reject) {
                //@todo use $eventManager
                this.vue.$root.$emit('reload-' + action.elementId, {
                    component: component,
                    data: data,
                    slots: action.slots ? action.slots : null
                });
                
                resolve();
            }.bind(this));
        },
        
        populateValue(action, component, data = {})
        {
            action.names.forEach(function (name) {
                this.vue.$eventManager().setFieldValue(name, component.value_)
            }.bind(this));
        },
        
        clickByClass(action, component, data = {})
        {
            Array.from(document.getElementsByClassName(action.className)).forEach(function (element) {
                element.click();
            });
        },

        click(action, component, data = {})
        {
            document.querySelector(action.selector).click();
        },
        
        copyValueByClass(action, component, data = {})
        {
            Array.from(document.getElementsByClassName(action.className)).forEach(function (element) {
                if (element.type == 'checkbox')
                {
                    Boolean(element.checked) != Boolean(component.value_) ? element.click() : null;
                } else
                {
                    element.setAttribute('value', component.value_);
                }
            });
        },
        
        redirect(action, component, data = {})
        {
            let url;
            if (action.type == 'simple')
            {
                let urlBase = new URL(currentUrl);
                url = new URL(action.url, urlBase.origin + urlBase.pathname)
            } else
            {
                let urlParam = typeof component._props.data[action.url] !== 'undefined' ? component._props.data[action.url] : null;
                url = new URL('', urlParam);
            }
            
            if (action.params && component && component._props && component._props.data)
            {
                Object.entries(action.params).forEach(function (entry) {
                    const [key, value] = entry;
                    
                    if (typeof component._props.data[value.substring(1)] === 'undefined')
                    {
                        return;
                    }
                    
                    url.searchParams.append(key, component._props.data[value.substring(1)]);
                }.bind(this));
            }
            
            if (action.params && component && component.data_)
            {
                Object.entries(action.params).forEach(function (entry) {
                    const [key, value] = entry;
                    
                    if (typeof component._props.data[value] === 'undefined')
                    {
                        return;
                    }
                    
                    url.searchParams.append(key, component._props.data[value]);
                }.bind(this));
            }
            
            if (action.target == 'new' || (data && data.middleButton))
            {
                window.open(url.href, "_blank", typeof action.targetData != "undefined" ? action.targetData : "");
            } else
            {
                document.location = url.href;
            }
        },
        
        redirectToPreviousPage(action, component, data = {})
        {
            history.back();
        },
        
        castForm(action, component, data = {})
        {
            const sourceForm = $(action.sourceFormSelector)[0];
            let formData = new FormData(sourceForm);
            
            let sourceEntries = {};
            
            for (const pair of formData.entries())
            {
                sourceEntries[pair[0]] = pair[1];
            }
            
            return this.vue.$request().post({
                namespace: action.targetComponent.slots.namespace,
                cid: action.targetComponent.slots.cid,
                ...extraParams,
                formData: {
                    ...sourceEntries
                },
                actionElementId: component.data && component.data.id ? component.data.id : null
            });
        },
        
        passAjaxData(action, component, data = {})
        {
            this.vue.$eventManager().passAjaxData(action.elementId, {
                ...data,
                ...component.getComponentData_(),
                ...(action.data ? action.data : []),
            });
        },
        
        alert(action, component, data = {})
        {
            alert(action.message);
        },
        
        collapse(action, component, data = {})
        {
            collapsableElement = $("#" + action.elementId);
            
            if (Array.isArray(action.visibleOnValues) && action.visibleOnValues.length > 0)
            {
                if (action.visibleOnValues.includes(component.value_))
                {
                    collapsableElement.collapse('show');
                } else
                {
                    collapsableElement.collapse('hide');
                }
                return;
            }
            
            if (Array.isArray(action.collapsedOnValues) && action.collapsedOnValues.length > 0)
            {
                if (action.collapsedOnValues.includes(component.value_))
                {
                    collapsableElement.collapse('hide');
                } else
                {
                    collapsableElement.collapse('show');
                }
                return;
            }
            
            if (collapsableElement.length > 0)
            {
                collapsableElement.collapse('toggle')
            }
        },
        
        pushToStore: function (action, component, data = {})
        {
            this.vue.$store.state[action.name] = action.data;
        },

        copyTextInline(elementId, text)
        {
            this.vue.$root.$emit('copy-text-inline-' + elementId, text);
        },
        
    }
