const EventManager =
    {
        install(Vue, options)
        {
            let self = this;
            Vue.prototype.$eventManager = function () {
                return self.installVue(this.$root);
            }
            
            this.listeners = [];
        },
        
        installVue(vue)
        {
            this.vue = vue;
            
            return this;
        },
        
        /******************* COMPONENTS ************************/
        onReload: function (component, callable, id = '') {
            this.addListenerToList_(component, 'reload-' + id, callable);
            this.vue.$on('reload-' + id, callable);
        },
        
        // reload: function (elementId, data = {}, details = {}) {
        //     this.vue.$emit('reload-' + elementId, {
        //         component: component,
        //         data: data,
        //         details: details
        //     });
        // },
        /*************** FORMS **************************/
        onSubmitFormEvent: function (component, callable, formId) {
            this.addListenerToList_(component, 'submit-form-' + formId, callable);
            this.vue.$on('submit-form-' + formId, callable);
        },
        /*************** MODALS **************************/
        onModalCloseEvent: function (component, callable, modalId) {
            this.addListenerToList_(component, 'close-modal-' + modalId, callable);
            this.vue.$on('close-modal-' + modalId, callable);
        },
        /**************** FIELD ERRORS ******************/
        setFieldError(fieldName, error)
        {
            this.vue.$emit('on-field-error-' + fieldName, error);
        },
        
        onSetFieldError: function (fieldName, callable) {
            this.vue.$on('on-field-error-' + fieldName, callable);
        },
        onResetFormErrors: function (callable) {
            this.vue.$on('reset-form-errors', callable);
        },
        
        resetFieldErrors()
        {
            this.vue.$emit('reset-form-errors');
        },
        
        /************** FIELD VALUES ********************/
        setFieldValue(fieldName, value)
        {
            this.vue.$emit('set-value-' + fieldName, value);
        },
        
        setFieldValueById(fieldId, value)
        {
            this.vue.$emit('set-value-by-id-' + fieldId, value);
        },
        
        onSetFieldValue(fieldName, callback)
        {
            this.vue.$on('set-value-' + fieldName, callback);
        },
        onSetFieldValueById(fieldId, callback)
        {
            this.vue.$on('set-value-by-id-' + fieldId, callback);
        },
        /******************* AJAX PARAMETERS ***********************/
        passAjaxData: function (id, params) {
            this.vue.$emit('pass-ajax-data-' + id, params);
        },
        
        onPassAjaxData: function (component, callable, id = '') {
            this.addListenerToList_(component, 'pass-ajax-data-' + id, callable);
            
            this.vue.$on('pass-ajax-data-' + id, callable);
        },

        /******************** GENERAL ACTIONS ************************/
        onCopyTextInline: function (component, callable, componentId) {
            this.addListenerToList_(component, 'copy-text-inline-' + componentId, callable);
            this.vue.$on('copy-text-inline-' + componentId, callable);
        },
        
        /******************** MANAGE COMPONENT EVENTS ************************/
        addListenerToList_: function (component, listener, fn) {
            if (!this.listeners[component.uniq])
            {
                this.listeners[component.uniq] = [];
            }
            
            this.listeners[component.uniq].push({
                event: listener,
                fn: fn
            });
        },
        
        clear: function (component) {
            if (!this.listeners[component.uniq])
            {
                return;
            }
            
            this.listeners[component.uniq].forEach(function (el) {
                this.vue.$off(el.event, el.fn);
            }.bind(this));
        }
    }
