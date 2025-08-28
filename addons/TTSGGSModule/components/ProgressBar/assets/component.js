var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'fill',
            'text',
            'size',
            'backgroundClass',
            'descriptionTooltip',
            'disableLabel',
            'enableFillMark',
        ],
        
        data: function () {
            return {
                fill_: 0,
                text_: '',
                size_: '',
                backgroundClass_: '',
                descriptionTooltip_: null,
                disableLabel_: false,
                enableFillMark_: false
            }
        },
        
        created: function () {
        },
    
        methods: {
        },
        
        computed: {
            fillStyle: function () {
                return "width: " + this.fill_ + "%";
            },
    
            sizeClass: function () {
                return "lu-progress--" + this.size_;
            },

            fillMark: function () {
                return this.enableFillMark_ ? this.fill + '%' : "";
            },
        },
    }