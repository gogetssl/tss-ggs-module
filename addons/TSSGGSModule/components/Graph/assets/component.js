var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        props: [
            'options',
            'width',
            'height',
            'title',
            'icon',
            'config',
            'elements'
        ],
        data: function () {
            return {
                options_: {},
                chart_: null,
                width_: '',
                height_: '',
                title_: '',
                icon_: '',
                config_: null,
                elements_: []
            };
        },
        mounted(){
            this.loadComponentScript('Graph', 'js/chart.min.js');
        },
        created: function () {
            this.waitFor('ApexCharts').then(() => {
                this.createChart();
            });
        },
        computed: {
            iconClass: function () {
            
            },

            showHeader: function () {
                return this.hasTitle() || this.hasElements();
            },

            isSparkline: function () {
                return this.options_.chart && this.options_.chart.sparkline && this.options_.chart.sparkline.enabled;
            },
        },
        methods: {
            ajaxLoaded_: function () {
                this.updateChart();
            },

            hasTitle: function () {
                return typeof this.title_ != "undefined" && this.title_.length > 0;
            },

            hasElements: function () {
                return typeof this.elements_.elements != "undefined" && this.elements_.elements.length > 0;
            },

            onReload: function (input = null) {
                this.propagateSlotsData_(input.slots ? input.slots : []);

                this.config_ = input.data ? input.data : {};
                this.loadDataFromServer_(this.config_, this.ajaxData_).then(this.updateChart);
            },
            
            createChart: function () {
                this.$nextTick(function () {

                    this.chart_ = new ApexCharts(document.querySelector("#" + this.cid_ + " .chartCanvas"), this.options_);
                    this.chart_.render();

                    this.updateChart();
                });
            },
            
            updateChart: function () {
                if (this.chart_)
                {
                    this.fixDataStructure(this.options_);
                    this.chart_.updateOptions(this.options_);
                }
            },

            fixDataStructure: function (options) {
                for (const key in options) {
                    if (options.hasOwnProperty(key)) {
                        if (typeof options[key] === 'string' && options[key].startsWith('function')) {
                            try {
                                options[key] = new Function('return ' + options[key])();
                            } catch (e) {
                            }
                        } else if (typeof options[key] === 'object' && options[key] !== null) {
                            this.fixDataStructure(options[key]);
                        }
                    }
                }
            }
        }
    }