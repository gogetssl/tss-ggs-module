var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                moduleName_: '',
                moduleVersion_: '',
                hideModuleVersion_: false,
            }
        },
        props: [
            'moduleName',
            'moduleVersion',
            'hideModuleVersion',
        ],
        created: function () {
        },
        computed: {
            moduleUrlClassLogo: function () {
                return 'lu-product-' + this.moduleIcon_ + '-for-whmcs'
            },
            moduleVersionInfo: function () {
                return this.translate_('module_version') + ' ' + this.moduleVersion_;
            },
        }
    }