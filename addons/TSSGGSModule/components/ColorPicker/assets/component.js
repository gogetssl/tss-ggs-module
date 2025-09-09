var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField],
        template: '#template-name#',
        mounted()
        {
            this.loadComponentScript('ColorPicker', 'js/jscolor.min.js');
        },
        created: function () {
            this.$nextTick(function () {
                this.waitFor('JSColor', () => {
                    new JSColor(this.$refs.input);
                });
            }.bind(this));
        },
    }