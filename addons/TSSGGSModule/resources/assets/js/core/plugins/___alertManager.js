const AlertManager =
    {
        install(Vue, options)
        {
            let self = this;
            Vue.prototype.$alertManager = function () {
                return self.installVue(this);
            }
        },
        
        installVue(vue)
        {
            this.$root = vue;
            return this;
        },
        
        error(message)
        {
            this.add(message, 'error');
        },
        
        success(message)
        {
            this.add(message, 'success');
        },
        
        add(message, type)
        {
            type = (type === 'error') ? 'danger' : type;
            layers2.alert.create({
                $alertPosition: 'right-top',
                $alertStatus: type,
                $alertBody: message,
                $alertTimeout: 10000
            });
        }
    }