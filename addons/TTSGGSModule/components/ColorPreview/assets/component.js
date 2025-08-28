var component =
    {
        extends: BaseDataComponent,
        mixins: [AjaxMixin],
        template: '#template-name#',
        data: function () {
            return {
                color_: '',
                squareShape_: false,
            }
        },
        props: [
            'color',
            'squareShape',
        ],
        created: function () {
        },
        computed: {
            styles: function () {
                let styles = {};
                styles["background-color"] = this.color_;
                styles["border-radius"] = this.squareShape_ ? 0 : "50%";
                return styles;
            }
        }
    }