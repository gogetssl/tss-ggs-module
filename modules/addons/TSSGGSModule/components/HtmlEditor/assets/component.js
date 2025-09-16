var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ActionsHandlerMixin],
        template: '#template-name#',
        props: [
            "autoSaveEnabled",
            "emoticonsEnabled",
        ],
        data()
        {
            return {
                tinymce: null,
                autoSaveEnabled_: false,
                emoticonsEnabled_: false
            }
        },
        mounted()
        {
            this.loadComponentScript('HtmlEditor', 'js/tinymce.min.js');
        },
        created: function () {
            this.$nextTick(function () {
                this.waitFor('tinymce').then(() => {
                    this.tinymce = tinymce.init({
                        selector: '[data-id= "' + this.cid_ + '"]',
                        plugins: this.getPlugins(),
                        imagetools_cors_hosts: ['picsum.photos'],
                        menubar: 'file edit view insert format tools table help',
                        toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
                        toolbar_sticky: true,
                        autosave_ask_before_unload: true,
                        autosave_interval: "30s",
                        autosave_prefix: "{path}{query}-{id}-",
                        license_key: 'gpl',
                        autosave_restore_when_empty: false,
                        autosave_retention: "2m",
                        image_advtab: true,
                        promotion: false,
                        content_css: '//www.tiny.cloud/css/codepen.min.css',
                        link_list: [
                            {title: 'My page 1', value: 'http://www.tinymce.com'},
                            {title: 'My page 2', value: 'http://www.moxiecode.com'}
                        ],
                        image_list: [
                            {title: 'My page 1', value: 'http://www.tinymce.com'},
                            {title: 'My page 2', value: 'http://www.moxiecode.com'}
                        ],
                        image_class_list: [
                            {title: 'None', value: ''},
                            {title: 'Some class', value: 'class-name'}
                        ],
                        importcss_append: true,
                        file_picker_callback: function (callback, value, meta) {
                            /* Provide file and text for the link dialog */
                            if (meta.filetype === 'file')
                            {
                                callback('https://www.google.com/logos/google.jpg', {text: 'My text'});
                            }
                            
                            /* Provide image and alt text for the image dialog */
                            if (meta.filetype === 'image')
                            {
                                callback('https://www.google.com/logos/google.jpg', {alt: 'My alt text'});
                            }
                            
                            /* Provide alternative source and posted for the media dialog */
                            if (meta.filetype === 'media')
                            {
                                callback('movie.mp4', {source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg'});
                            }
                        },
                        height: 520,
                        image_caption: true,
                        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
                        noneditable_noneditable_class: "mceNonEditable",
                        toolbar_mode: 'sliding',
                        contextmenu: "link image imagetools table",
                        setup: (editor) => {
                            editor.on('init', () => {
                                if (this.value_ !== null)
                                {
                                    editor.setContent(this.value_);
                                }
                            });
                            editor.on('keyup', () => {
                                this.value_ = editor.getContent({format: 'html'});
                            });
                            editor.on('change', () => {
                                this.value_ = editor.getContent({format: 'html'});
                            });
                            editor.on('nodeChange', () => {
                                this.value_ = editor.getContent({format: 'html'});
                            });
                            editor.on('focus', () => {
                                //Trigger global 'focusin' event
                                const textArea = $('[data-id= "' + this.cid_ + '"]');
                                
                                if (textArea.length <= 0)
                                {
                                    return;
                                }
                                
                                const element = textArea[0];
                                
                                let eventType = "onfocusin" in element ? "focusin" : "focus";
                                let bubbles = "onfocusin" in element;
                                let event;
                                
                                if ("createEvent" in document)
                                {
                                    event = document.createEvent("Event");
                                    event.initEvent(eventType, bubbles, true);
                                } else if ("Event" in window)
                                {
                                    event = new Event(eventType, {bubbles: bubbles, cancelable: true});
                                }
                                
                                element.focus();
                                element.dispatchEvent(event);
                            });
                        },
                    });
                });
            })
        },
        
        beforeDestroy: function () {
            const editor = tinymce.get(this.cid_);
            if (editor && editor.initialized)
            {
                tinymce.remove(editor);
            }
        },
        
        methods: {
            getPlugins: function () {
                var plugins = 'preview importcss searchreplace autolink save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars';
                
                if (this.autoSaveEnabled_)
                {
                    plugins += " autosave";
                }
                
                if (this.emoticonsEnabled_)
                {
                    plugins += " emoticons";
                }
                
                return plugins;
            }
        }
    }
