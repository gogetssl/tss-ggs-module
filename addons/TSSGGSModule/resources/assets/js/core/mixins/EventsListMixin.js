const EventsListMixin =
    {
        created()
        {
            this.$eventManager().onReload(this, this.onReload, this.cid);
            this.$eventManager().onPassAjaxData(this, this.onPassAjaxData, this.cid);
            //this.$eventManager().onGetAjaxData(this, this.onGetAjaxData, this.cid);
        },
        updated()
        {
            this.$emit('updated', this);
        },
        methods: {
            onReload: function (data = {}) {
                this.propagateSlotsData_(data.slots ? data.slots : []);
                this.loadDataFromServer_({}, this.ajaxData_);
            },
            onPassAjaxData: function (data = {}) {
                this.propagateSlotsData_({
                    'ajaxData': data
                });
            },
            resetErrors: function () {
                this.$eventManager().resetFieldErrors()
            }
        },
        beforeDestroy()
        {
            this.$eventManager().clear(this);
        },
    }
