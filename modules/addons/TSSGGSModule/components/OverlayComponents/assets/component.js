var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        data: function () {
            return {}
        },
        props: [
            'text',
            'css',
            'elements',
            'content'
        ],
        computed: {
            components: function () {
                this.$forceUpdate();
                return this.$store.getters.getOverlayComponents;
            }
        },
        

        methods: {
            reloadParent: function (input = null) {
                this.$store.getters.getOverlayComponents[0].parent.$emit('reload-parent', input);
            }
        },
    }