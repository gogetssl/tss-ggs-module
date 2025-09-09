const ResponseValidator = {
    install(Vue, options)
    {
        let self = this;
        Vue.prototype.$responseValidator = function () {
            return self.installVue(this.$root);
        }
    },
    
    installVue(vue)
    {
        this.vue = vue;
        return this;
    },
    
    validate(data)
    {
        if (data.status == 'error')
        {
            return this.validateErrors(data);
        }
        
        return this.validateSuccess(data);
    },
    validateErrors(data)
    {
        if (data.message)
        {
            this.vue.$alertManager().error(data.message);
        }
        
        if (data.data && data.data.FormValidationErrors)
        {
            this.validateFormErrors(data);
        }
    },
    validateFormErrors(data)
    {
        for (fieldName in data.data.FormValidationErrors)
        {
            let errors = data.data.FormValidationErrors[fieldName];
            for (key in errors)
            {
                this.vue.$eventManager().setFieldError(fieldName, errors[key])
            }
        }
    },
    validateSuccess(data)
    {
        if (data.message)
        {
            this.vue.$alertManager().success(data.message);
        }
    }
}
