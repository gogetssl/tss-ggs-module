const FormField = {
    mixin: [DataInitializerMixin],
    data: function () {
        return {
            name_: '',
            value_: null,
            defaultValue_: '',
            css_: '',
            disabled_: '',
            placeholder_: '',
            readonly_: '',
            autocomplete_: ''
        }
    },
    props: [
        'name',
        'value',
        'css',
        'disabled',
        'defaultValue',
        'placeholder',
        'readonly',
        'autocomplete'
    ],
    updated: function () {
    },
    created: function () {
        this.$eventManager().onSetFieldValue(this.name_, function (value) {
            this.value_ = value;
        }.bind(this));
        
        this.$eventManager().onSetFieldValueById(this.cid, function (value) {
            this.value_ = value;
        }.bind(this))
        
        //set default valur
        if (this.value_ === null && this.defaultValue_ !== '')
        {
            this.value_ = this.defaultValue_;
        }
    },
    methods: {
        change: function (event) {
            this.emitEvents(event);
        },
        
        emitEvents: function (event) {
            this.$emit('change', {
                name: this.name_,
                value: this.value_
            })
        },
    }
}
