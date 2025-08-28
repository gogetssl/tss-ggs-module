var component =
    {
        extends: BaseDataComponent,
        mixins: [ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            'data',
            'title',
            'imageName',
            'clickEvent',
            'click',
            'autoload_data_after_created',
            'actions',
            'img',
            'id',
            'img',
            'mode',
            'overlayIcons',
        ],
        data: function () {
            return {
                show_title: true,
                data_: [],
                title_: '',
                imageName_: '',
                clickEvent_: '',
                img_: '',
                id_: '',
                mode_: false,
                overlayIcons_: [],
            };
        },
        methods: {
            getIconClass: function () {
                return 'lu-mdi mdi mdi-' + this.overlayIcons_[this.mode_];
            },
            onClick: function (event = null) {
                if (this.mode_ === 'mode_manage') {
                    this.runAction(event, 'onClick');
                } else if (this.mode_ === 'mode_select') {
                    this.$emit('change', { 'imageName':this.imageName_ , 'imageUrl':this.img_});
                }
            },
            getItemClass: function () {
                return this.mode_ + (this.mode_ === 'mode_select' && this.imageName_ === this.$parent.$parent.value_ ? " selected" : "");
            },
        },
    }