var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField],
        template: '#template-name#',
        props: [
            'min',
            'max',
            'step'
        ],
        data: function () {
            return {
                min_: '',
                max_: '',
                step_: ''
            };
        },
        methods: {},
    }