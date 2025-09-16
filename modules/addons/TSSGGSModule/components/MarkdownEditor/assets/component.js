var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            "autoSaveEnabled"
        ],
        data()
        {
            return {
                simplemde: null,
                autoSaveEnabled_: false
            }
        },
        mounted()
        {
            this.loadComponentScript('MarkdownEditor', 'js/marked.min.js');
            this.loadComponentScript('MarkdownEditor', 'js/simplemde.min.js');
        },
        created: function () {
            this.$nextTick(function () {
                this.waitFor('SimpleMDE').then(() => {
                    this.simplemde = new SimpleMDE({element: document.getElementById(this.cid_), autosave: {enabled: this.autoSaveEnabled_, uniqueId: this.cid_}, spellChecker: false});
                    
                    if (this.value_ !== null)
                    {
                        this.simplemde.value(this.value_)
                    } else
                    {
                        this.value_ = this.autoSaveEnabled_ ? this.value_ : this.simplemde.value();
                    }
                    
                    this.simplemde.codemirror.on("change", () => {
                        this.value_ = this.simplemde.value()
                    });
                });
            })
            
            this.$eventManager().onCopyTextInline(this, this.copyTextInline, this.cid);
        },
        
        methods: {
            copyTextInline: function (text) {
                const pos = this.simplemde.codemirror.getCursor();
                this.simplemde.codemirror.setSelection(pos, pos);
                this.simplemde.codemirror.replaceSelection(text);
            },
        },
    }
