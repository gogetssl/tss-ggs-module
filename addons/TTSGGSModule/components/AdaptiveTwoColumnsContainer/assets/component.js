var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                text_: '',
                css_: '',
                elements_: '',
                content_: '',
                timeout_: ''
            }
        },
        props: [
            'text',
            'css',
            'elements',
            'content',
        ],
        methods:{
            updated: function(el){
                clearTimeout(this.timeout_);
                this.timeout_ = setTimeout(function(){
                    $('.widgets').masonry().masonry('reloadItems');
                }, 500);
            },
        }
    }