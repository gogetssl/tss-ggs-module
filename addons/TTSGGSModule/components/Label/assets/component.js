var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin, TooltipMixin],
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                type_: '',
                displayAsStatusLabel_: '',
                backgroundColor_: '',
                textColor_: '',
                size_: '',
                title_:'',
            }
        },
        props: [
            'text',
            'type',
            'displayAsStatusLabel',
            'backgroundColor',
            'textColor',
            'size',
            'title',
        ],
        
        created: function () {
        },
        computed: {
            badgeType: function () {
                return 'lu-label--' + this.type_;
            },
            labelStatus: function () {
                return this.displayAsStatusLabel_ ? 'lu-label--status' : '';
            },
            labelSize: function () {
                return 'lu-label--' + (this.size_ ?  this.size_ : 'sm');
            },
            styles: function () {
                
                let styles = {};
                
                this.textColor_ ? (styles["color"] = this.textColor_) : '';
                this.backgroundColor_ ? (styles["background-color"] = this.backgroundColor_) : '';
                
                return styles;
            }
        }
    }