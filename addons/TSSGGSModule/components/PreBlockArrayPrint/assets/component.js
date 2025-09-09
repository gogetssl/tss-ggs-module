var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField],
        template: '#template-name#',
        methods: {
            syntaxHighlight: function (json) {
                json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                    let cls = 'number';
                    if (/^"/.test(match))
                    {
                        if (/:$/.test(match))
                        {
                            cls = 'key';
                        } else
                        {
                            cls = 'string';
                        }
                    } else if (/true|false/.test(match))
                    {
                        cls = 'boolean';
                    } else if (/null/.test(match))
                    {
                        cls = 'null';
                    }
                    return '<span class="' + cls + '">' + match + '</span>';
                });
            },
        },
        created: function () {
        },
        computed: {
            val: function () {
                return this.syntaxHighlight(JSON.stringify(this.value_, null, 2));
            }
        }
    }