const TooltipMixin = {
    created: function () {
        this.$nextTick(function () {
            if(!this.title_){
                return;
            }
            $("[tooltip-id='" + this.tooltip_id_ + "']").luTooltip({});
        }.bind(this));
    },
    destroyed: function () {
        $(".drop.lu-tooltip.drop-element").hide();
    },
    data: function () {
        return {
            tooltip_id_: (performance.now().toString(36) + Math.random().toString(36)).replace(/\./g, "")
        }
    }
}