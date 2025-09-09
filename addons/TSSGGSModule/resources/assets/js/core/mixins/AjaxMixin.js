const AjaxMixin = {
    mixins: [Data],
    props: [
        'ajaxOnLoad',
        'ajaxAutoSubmit',
        'ajaxOnAction',
        'ajaxAutoReloadInterval',
        'ajaxProviderAction',
        'ajaxData',
        'ajaxStoreNames',
        'showPreloader',
        'loading',
        'csrfProtection',
        'integrityCheckToken'
    ],
    data: function () {
        return {
            loading_: false,
            ajaxOnLoad_: false,
            ajaxAutoSubmit_: false,
            ajaxOnAction_: false,
            ajaxAutoReloadInterval_: 0,
            ajaxProviderAction_: '',
            ajaxData_: [],
            ajaxStoreNames_: [],
            showPreloader_: false,
            csrfProtection_: '',
            integrityCheckToken_: '',
        };
    },
    created()
    {
        if (this.ajaxOnLoad_)
        {
            this.runAjaxOnAction_();
        }
        
        if (this.ajaxAutoReloadInterval_)
        {
            setInterval(this.runAjaxOnAction_, this.ajaxAutoReloadInterval_);
        }
    },
    mounted()
    {
        if (this.ajaxAutoSubmit_ && typeof this.submit == "function")
        {
            this.submit();
        }
    },
    methods: {
        runAjaxOnAction_: function (data) {
            return this.loadDataFromServer_({
                formData: {
                    ...this.data_,
                    ...data,
                },
                providerAction: this.ajaxProviderAction_
            }, this.ajaxData_,).then(typeof this.ajaxLoaded_ === 'function' ? this.ajaxLoaded_ : null);
        },
        loadDataFromServer_: function (data = {}, ajaxData = {}) {
            return new Promise(function (resolve, reject) {
                this.loading_ = true;
                this.$request().post({
                    ...data,
                    ...extraParams,
                    ...{
                        cid: this.cid,
                        namespace: this.namespace,
                        ajaxData: ajaxData,
                        storeData: this.ajaxStoreNames_ ? Object.fromEntries(this.ajaxStoreNames_.map(val => [val, this.$root.$store.state[val]])) : null,
                        csrfProtection: this.csrfProtection_,
                        integrityCheckToken: this.integrityCheckToken_
                    },
                }).then(function (data) {
                        this.loading_ = false;
                        this.$responseValidator().validate(data.data);
                        this.$actionHandler().handle(data.data.actions ? data.data.actions : [], this);
                        this.processSlotsFromResponse_(data.data);
                        resolve(data);
                    }.bind(this)
                ).catch(function (e) {
                        reject(e);
                    }.bind(this)
                ).finally(function () {
                    this.loading_ = false;
                }.bind(this));
            }.bind(this));
            
        },
    }
}