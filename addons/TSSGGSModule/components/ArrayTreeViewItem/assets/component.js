var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                css_: '',
                name_: '',
                value_: null,
                hasElements_: false,
                showElements_: false,
                elementsPrefix_: null,
                keyValueSeparator_: null,
                elementsExpander_: null,
                expanderOnBeginning_: false,
                hiddenKeysMode_: false,
            }
        },
        props: [
            'css',
            'name',
            'value',
            'hasElements',
            'elementsPrefix',
            'keyValueSeparator',
            'elementsExpander',
            'expanderOnBeginning',
            'hiddenKeysMode',
        ],
        created: function () {
        },
        methods: {
            toggleElementsContainer: function () {
                this.showElements_ = !this.showElements_;
            },
        },

    }