const ActionsHandlerMixin = {
    props: [
        'actions'
    ],
    data: function () {
        return {
            actions_: [],
            isLoading_: false,
        }
    },
    methods: {
        onClick: function (event = null) {
            this.runAction(event, 'onClick');
        },
        onClickMiddle: function (event) {
            this.runAction(event, 'onClick', {middleButton: true});
        },
        onChange: function (event = null) {
            this.$nextTick(function () {
                this.runAction(event, 'onChange');
            }.bind(this));
        },
        onClose: function (event = null) {
            this.runAction(event, 'onClose');
        },
    
        runAction(event, action, data)
        {
            if (!this.actions_ || !this.actions_[action])
            {
                if (event)
                {
                    this.$emit(event.type);
                }
                return
            }
        
            event ? event.preventDefault() : null;
            this.isLoading_ = true;
        
            this.$actionHandler().handle(this.actions_[action], this, data).then(function () {
                this.isLoading_ = false;
            }.bind(this)).catch(function () {
                this.isLoading_ = false;
            }.bind(this));
        }
    }
}