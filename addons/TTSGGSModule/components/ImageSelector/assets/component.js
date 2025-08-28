var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        mixins: [FormField],
        data: function () {
            return {
                mediaLibrary_: false,
                imageUrl_: "",
            }
        },
        props: [
            'mediaLibrary',
            'imageUrl',
        ],
        methods: {
            change: function(e) {
                this.value_ = e.imageName;
                this.imageUrl_ = e.imageUrl;
            },
            mediaLibraryLoaded: function(items) {
                if (this.value_) {
                    items.forEach(function (entry) {
                        if (entry.slots.imageName === this.value_) {
                            this.imageUrl_ = entry.slots.img;
                        }
                    }.bind(this));
                }
            },
            removeSelectedImage: function(e) {
                this.value_ = null;
            },
            getContentUrl: function(e) {
                this.value_ = e.imageName;
            },
            ajaxLoaded_: function () {
                // console.log("weszlo")
                // if (this.value_) {
                //     console.log("weszlo")
                //     this.mediaLibrary_.elements.forEach(function (entry) {
                //         console.log(entry)
                //     });
                //     // console.log(this.mediaLibrary_)
                // }
            },
        }
    }