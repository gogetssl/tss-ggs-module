var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'error',
            'trace',
            'file',
            'line'
        ],
        data: function () {
            return {
                error_: '',
                trace_: '',
                file_:'',
                line_:''
            };
        },
    }