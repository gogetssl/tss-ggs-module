var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                title_: '',
                url_: '',
                target_: null,
                icon_: '',
                active_: '',
                elements_: ''
            }
        },
        props: [
            'title',
            'url',
            'target',
            'icon',
            'active',
            'elements'
        ],
        computed: {
            iconClass: function () {
                return this.icon_ ? 'mdi mdi-' + this.icon_ : ''
            },
            getTarget: function () {
                return this.target_ ? this.target_ : false;
            },
        }
    }