var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                elements_: [],
                name_: '',
                value_: '',
                text_: '',
                classes_: '',
                css_: '',
                style_: '',
                strengthIndicatorEnabled_: false
            }
        },
        props: [
            'name',
            'value',
            'text',
            'classes',
            'css',
            'strengthIndicatorEnabled'
        ],
        mounted()
        {
            this.loadComponentScript('FormPasswordGenerator', 'js/zxcvbn.min.js');
        },
        created: function () {
            if (this.strengthIndicatorEnabled_)
            {
                this.waitFor('zxcvbn').then(() => {
                    $("#" + this.elements_.elements[0].cid).on("input", (e) => {
                        this.checkPasswordStrength(e.target.value)
                    });
                });
            }
        },
        methods: {
            checkPasswordStrength: function (input)
            {
                if (input.length == 0)
                {
                    this.style_ = "";
                    return;
                }
                
                var result = zxcvbn(input);
                var score = parseInt(result.score);
                var score_to_color = ['#c9302c', '#d9534f', '#ec971f', '#5cb85c', '#449d44'];
                this.style_ = 'border-bottom: 3px solid' + score_to_color[score];
            }
        }
        
    }