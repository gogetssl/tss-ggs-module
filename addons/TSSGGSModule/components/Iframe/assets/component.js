var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {
                css_: '',
                elements_: '',
                content_: ''
            }
        },
        props: [
            'css',
            'elements',
            'content'
        ],
        created: function () {
            this.content_ = this.content_ + "<p>&nbsp</p>";
        },
        mounted() {
            var iFrame = document.getElementById(this.cid_ );
            
            if (typeof iFrame != "undefined")
            {
                iFrame.onload = function()
                {
                    iFrame.style.height = iFrame.contentWindow.document.body.scrollHeight + 'px';
                    iFrame.style.width  = iFrame.contentWindow.document.body.scrollWidth+'px';
                }
            }
        }
        
    }