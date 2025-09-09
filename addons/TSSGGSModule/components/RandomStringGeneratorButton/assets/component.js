var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'classes',
            'type',
            'title',
            'size',
            'clickEvent',
            'fieldToUpdate',
            'alphabet',
            'length',
            'css',
        ],
        data: function () {
            return {
                clickEvent_: 'click',
                fieldToUpdate_: '',
                length_: '',
                alphabet_: '',
                title_: '',
                css_: ''
            };
        },
        created: function () {
        },

        methods: {
            click: function (event) {
                event.preventDefault();

                this.$eventManager().setFieldValueById(this.fieldToUpdate_, this.generatePassword());

                this.$nextTick(() => {
                    $("#" + this.fieldToUpdate_).trigger("input");
                });
            },
            generatePassword: function () {
                let alphabetLength = this.alphabet_.length;
                let password = '';
                for (let i = 0; i < this.length; i++)
                {
                    password += this.alphabet_.charAt(Math.floor(Math.random() * alphabetLength));
                }
                
                return password;
            }
        }
    }