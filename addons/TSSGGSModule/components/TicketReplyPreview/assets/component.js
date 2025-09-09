var component =
    {
        extends: BaseDataComponent,
        mixins: [FormField, ActionsHandlerMixin, Data, AjaxMixin, ComponentsContainer],
        template: '#template-name#',
        data: function () {
            return {
                replyId_: '',
                css_: '',
                userName_: '',
                message_: '',
                posted_: '',
                avatarImage_: null,
                attachments_: [],
                elements_: [],
                storagePath_: '',
                storageAttachments_: [],
            }
        },
        props: [
            'replyId',
            'css',
            'userName',
            'message',
            'posted',
            'avatarImage',
            'attachments',
            'elements',
            'storageAttachments',
            'storagePath',
        ],
        created: function () {

        },
        
        methods: {
            deleteAttachment: function(uri) {
                if (!window.confirm(this.translations_['delete_attachment_message']))
                {
                    return;
                }
                
                fetch(uri)
                     .then(() => this.reloadParent())
                    .catch(err => console.log(err));
            },
    
            reloadParent: function(e) {
                this.onChange();
            },
            
            showImage: function (imageUrl, imageName) {
                this.initPopup(imageUrl, imageName)
                this.initClosePopupAction()
            },
            
            initPopup: function (imageUrl, imageName) {
                const popup = document.createElement('popup')
                const container = document.createElement('div')
                const imageContainer = document.createElement('div')
                const closeButton = document.createElement('span')
                const imageNameContainer = document.createElement('span')
                const img = document.createElement('img')
                
                imageNameContainer.innerHTML = imageName;
                imageNameContainer.className  = "lu-d-block text-center lu-badge--light-overlay";
                
                img.src = imageUrl
    
                imageContainer.className = "lu-d-inline-block lu-border-style-solid lu-border-white lu-avatar--square";
                imageContainer.appendChild(img);
                
                closeButton.className = 'lu-btn__icon lu-mdi mdi mdi-close lu-is-fixed text-center';
                closeButton.role = "button";
                
                container.appendChild(imageContainer)
                container.appendChild(closeButton)
                container.appendChild(imageNameContainer)
                
                popup.className = 'image-popup lu-modal show  modal--zoomIn lu-modal--info'
                popup.appendChild(container)
    
                document.getElementById('layers2').appendChild(popup)
            },
    
            initClosePopupAction() {
                $('.image-popup span.mdi-close').on('click', () => {
                    $('*').css('filter', 'none')
                    $('.popup-bg').remove()
                    $('.image-popup').remove()
                })
            },
    
    
        },
    }