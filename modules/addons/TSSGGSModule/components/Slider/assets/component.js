var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ComponentsContainer],
        template: '#template-name#',
        props: [
            'min',
            'max',
            'step',
            'type',
            'valuePreviewEnabled',
            'valuesMap',
        ],
        data: function () {
            return {
                min_: 0,
                max_: 100,
                step_: 1,
                type_: "",
                valuePreviewEnabled_: false,
                valuesMap_: []
            }
        },
        mounted: function () {
            this.buildProgressGradient();
        },
        methods: {
            change: function (event)
            {
                this.value_ = event.target.value;

                this.buildProgressGradient();
            },
            buildProgressGradient: function ()
            {
                const input = this.$el.getElementsByTagName('input')[0];

                if (typeof input == "undefined")
                {
                    return;
                }

                const stepsCount = (this.max_ - this.min_) / this.step_;

                const progress = ((this.value_ / this.step_ - this.min_)  / stepsCount) * 100;

                input.style.background = `linear-gradient(to right, var(--slider-thumb-color) ${progress}%, #e9ebf0 ${progress}%)`;
            }
        },
        computed: {
            typeClass: function () {
                return this.type_ ? 'lu-slider--' + this.type_ : '';
            },
            getValue: function () {
                return this.valuesMap_[this.value_] ? this.valuesMap_[this.value_] : this.value_;
            },
        }
    }