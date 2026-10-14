var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField],
        template: '#template-name#',
        created()
        {
      
        },
        props: [
            'css',
        ],
        data: function () {
            return {
                css_: [],
            };
        },
        watch: {
            value_: function (newVal, oldVal) {
   
            }
        }
    }