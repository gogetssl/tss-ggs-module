var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                moduleName_: '',
                moduleLogo_: '',
                vendorLogo_: '',
                mainUrl_: '',
                navbar_: null,
                moduleIcon_: '',
                moduleVersion_: '',
            }
        },
        props: [
            'moduleName',
            'moduleLogo',
            'mainUrl',
            'vendorLogo',
            'navbar',
            'moduleIcon',
            'moduleVersion',
        ],
        created: function () {
        },
        computed: {
            moduleUrlClassLogo: function () {
                return 'lu-product-' + this.moduleIcon_ + '-for-whmcs'
            },
            moduleAdditionalInfo: function () {
                return this.moduleName_ + ' ' + this.moduleVersion_;
            },
        }
    }