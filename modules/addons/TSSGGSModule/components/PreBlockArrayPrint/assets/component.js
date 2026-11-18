var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField],
        template: '#template-name#',
        methods: {
            syntaxHighlight: function (json, isError) {
                json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                let key = '';
                return json.replace(/("(\u[a-zA-Z0-9]{4}|\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                    let cls = 'number';
                    if (/^"/.test(match))
                    {
                        if (/:$/.test(match))
                        {
                            // the key in its own colour, the colon stays punctuation
                            key = match.replace(/\s*:$/, '');
                            return '<span class="key">' + key + '</span><span class="punct">:</span>';
                        }
                        cls = 'string';
                        // the values that tell what went wrong are marked: an error type, the message of an error entry
                        // and the exception class
                        if ((key === '"type"' && /^"(error|critical|alert|emergency)"$/i.test(match))
                            || (key === '"message"' && isError)
                            || (key === '"class"' && /(exception|error)"$/i.test(match)))
                        {
                            cls += ' alert';
                        }
                    } else if (/true|false/.test(match))
                    {
                        cls = 'boolean';
                    } else if (/null/.test(match))
                    {
                        cls = 'null';
                    }
                    key = '';
                    return '<span class="' + cls + '">' + match + '</span>';
                });
            },
        },
        created: function () {
        },
        computed: {
            val: function () {
                const entry   = this.value_ && this.value_.Entry ? this.value_.Entry : null;
                const isError = !!(entry && /^(error|critical|alert|emergency)$/i.test(String(entry.type || '')));
                return this.syntaxHighlight(JSON.stringify(this.value_, null, 2), isError);
            }
        }
    }
