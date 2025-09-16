const DataInitializerMixin = {
    
    data: function () {
        return {
            slotsToMerge_: [
                'ajaxData_'
            ],
            slotsToIgnoreJsonParse_: [
               /* 'value_'*/
            ],
        }
    },
    created: function () {
        this.loadDataFromProps_();
    },
    methods: {
        loadDataFromProps_: function () {
            this.propagateSlotsData_(this._props);
        },
        propagateSlotsData_: function (data) {
            
            
            for (let field in data)
            {
                let name = field + '_';
                
                if (name == 'ajaxData_')
                {
                    this[name] = {...this[name], ...data[field]};
                    
                    continue;
                }
                
                if (this.hasOwnProperty(name) && typeof data[field] != 'undefined')
                {
                    if(this.slotsToIgnoreJsonParse_.includes(name))
                    {
                        this[name] = data[field];
                        continue;
                    }
                    
                    try
                    {
                        this[name] = JSON.parse(data[field]);
                    } catch (ex)
                    {
                        this[name] = data[field];
                    }
                }
                
            }
        }
    },
    watch: {
        $props: {
            handler()
            {
                this.loadDataFromProps_();
            },
            deep: true,
            immediate: true,
        },
    },
}