var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'data',
            'classes',
            'type',
            'text',
            'icon',
            'title',
            'size',
            'copySelector',
            'copyType',
            'css',
            'title'
        ],
        data: function () {
            return {
                show_title: true,
                data_: null,
                icon_: '',
                type_: '',
                size_: '',
                copySelector_: '',
                copyType_: '',
                isCopied_: false,
                css_: '',
                title_: ''
            };
        },

        methods: {
            onClick: function (event) {
                event.preventDefault();
                element = this.copyType_ == 'id' ? document.getElementById(this.copySelector_) : document.getElementsByName(this.copySelector_)[0];
       
                element.select();
                element.setSelectionRange(0, 99999);
                
                navigator.clipboard.writeText(element.value).then(function () {
                    this.$alertManager().success(this.translate_('text_copied'));
                    this.isCopied_ = true;
                    
                    setTimeout(function () {
                        this.isCopied_ = false
                    }.bind(this), 3000);
                }.bind(this));
            },
        },
        computed: {
            typeClass: function () {
                return this.type_ ? 'lu-btn--' + this.type_ : '';
            },
            sizeClass: function () {
                return this.size_ ? 'lu-btn--' + this.size_ : '';
            },
            isCopiedClass: function () {
                return this.isCopied_ ? 'lu-btn--success' : '';
            },
        }
    }