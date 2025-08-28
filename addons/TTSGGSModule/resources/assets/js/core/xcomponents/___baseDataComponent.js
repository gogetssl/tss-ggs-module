const BaseDataComponent = {
    extends: BaseComponent,
    mixins: [DataInitializerMixin, ListenersComponentsMixin, EventsListMixin, AjaxMixin],
    props: [
        // 'data',
        'elements',
    ],
    methods: {
        processSlotsFromResponse_: function(data)
        {
            if (!data.data || !data.data.slots)
            {
                return;
            }
            
            for (key in data.data.slots)
            {
                this[key + '_'] = data.data.slots[key];
            }
        },
        getComponentData_: function() {
            if(typeof this.value_ !== 'undefined'){
                return {
                    value: this.value_
                }
            }
        },
    }
}