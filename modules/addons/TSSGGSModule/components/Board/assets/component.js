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
                queue_: [],
                delayedRequest_: []
            }
        },
        props: [
            'text',
            'css',
            'elements',
            'content'
        ],
        methods: {
            change: function (el) {
                if (this.delayedRequest_)
                {
                    clearTimeout(this.delayedRequest_);
                }
                this.queue_.push({
                    column: el.column,
                    event: el.event
                });
                
                this.delayedRequest_ = setTimeout(function () {
                    let queue = this.queue_;
                    this.queue_ = [];
                    this.loadDataFromServer_({
                            formDataBoard: {
                                changes: queue
                            },
                            providerAction: 'update'
                        }, this.ajaxData_
                    ).then(function () {
                    });
                }.bind(this), 2000);
            },
        },
    }