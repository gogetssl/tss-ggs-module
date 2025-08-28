var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'format',
            'type',
            'placeholder',
            'range',
            'editable',
            'clearable',
            'disableBeforeDate',
            'disableAfterDate',
            'disableBeforeTime',
            'disableAfterTime',
            'confirmButtonEnabled',
        ],
        
        data: function () {
            return {
                picker_: null,
                format_: '',
                type_: '',
                placeholder_: '',
                range_: false,
                editable_: true,
                clearable_: true,
                disableBeforeDate_: null,
                disableAfterDate_: null,
                disableBeforeTime_: null,
                disableAfterTime_: null,
                confirmButtonEnabled_: false,
            };
        },
        mounted(){
            this.loadComponentScript('DatePicker', 'js/vue2-datepicker.min.js');
        },
        created: function () {
            this.waitFor('DatePicker').then(() => {
                this.picker_ = DatePicker;
            });
        },
        destroyed: function () {
        },
        methods: {
            onChangeDate: function () {
                this.onChange();
            },
            disabledDate: function (date) {
                const beforeResult = this.disableBeforeDate_ !== null ? date < new Date(this.disableBeforeDate_).setHours(0,0,0,0) : false;
                const afterResult = this.disableAfterDate_ !== null ? date > new Date(this.disableAfterDate_).setHours(0,0,0,0) : false;

                return beforeResult || afterResult;
            },
            disabledTime: function (date) {

                const beforeResult = this.disableBeforeTime_ !== null ? date < new Date(this.disableBeforeTime_) : false;
                const afterResult = this.disableAfterTime_ !== null ? date > new Date(this.disableAfterTime_) : false;

                return beforeResult || afterResult;
            },
            getIconClass: function () {
                return "mdi-" + (this.type_ !== "time" ? "calendar" : "clock-outline");
            },
        }
    }