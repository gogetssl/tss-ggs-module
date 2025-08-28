var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        mixins: [FormField, ActionsHandlerMixin],
        props: [
            'value',
            'options',
            'setDefaultValueAsFirstOption',
        ],
        data: function () {
            return {
                value_: "",
                options_: [],
                setDefaultValueAsFirstOption_: false,
            }
        },
        created: function () {
        },
        methods: {

            selectVendor: function (){
                fetch('addonmodules.php?module=TTSGGSModule&mg-page=configuration&mg-action=step3', {
                    method: 'POST', // or PATCH or PUT or DELETE or GET
                    body: JSON.stringify({
                        test: 'test1',
                    }),
                }).then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                    .then((data) => {
                        console.log('New Post created:', data);
                    })
                    .catch((error) => {
                        console.error('Fetch error:', error);
                    });
            }

        }
    }