const TranslatorMixin = {
    props: [
        'translations'
    ],
    data()
    {
        return {
            translations_: ''
        }
    },
    methods: {
        translate_: function (key) {
            return this.translations_[key];
        },
    }
}