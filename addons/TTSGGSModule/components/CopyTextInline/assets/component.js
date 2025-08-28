var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'data',
            'hideIcon',
            'targetType',
            'target',
            'css',
            'title',
            'text'
        ],
        data: function () {
            return {
                data_: null,
                hideIcon_: false,
                targetType_: '',
                target_: '',
                css_: '',
                title_: '',
                text_:'',
                focusedElement_: null,
            };
        },
    
        created: function () {
            if (this.targetType_ === 'focused') {
                const component = this;
                
                $(document).on('focusin', ':input', function () {
                    component.focusedElement_ = $(this);
                });
            }
    
            $.fn.setCursorPosition = function(pos) {
                this.each(function(index, elem) {
                    if (elem.setSelectionRange) {
                        elem.setSelectionRange(pos, pos);
                    } else if (elem.createTextRange) {
                        var range = elem.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', pos);
                        range.moveStart('character', pos);
                        range.select();
                    }
                });
                return this;
            };
        },
        
        methods: {
            onClick: function (event) {
                event.preventDefault();
    
                switch (this.targetType_)
                {
                    case 'clipboard':
                        this.copyToClipboard(this.text_);
                        break;
                    case String(this.targetType_.match(/^component.*/i)):
                        this.copyToComponent($(this.getComponentSelector()));
                        break;
                    case 'focused':
                        this.copyToFocused();
                        break;
                    default:
                        this.copyToClipboard(this.text_);
                        break;
                }
            },
            
            copyToClipboard: function () {
                navigator.clipboard.writeText(this.text_).then(function () {
                    this.$alertManager().success(this.translate_('text_copied'));
                }.bind(this));
            },
    
            copyToComponent: function (targetComponent) {
                if (targetComponent !== null && typeof targetComponent === 'object') {

                    //Copy for TinyMCE editor
                    if (targetComponent.hasClass("lu-html-editor-textarea"))
                    {
                        tinymce.activeEditor.execCommand('mceInsertContent', false, this.text_);
                        return;
                    }

                    //Copy for SimpleMDE editor
                    if (targetComponent.closest("div.simplemde").length > 0)
                    {
                        const mainContainer = targetComponent.closest("div.simplemde");
                        targetComponent = mainContainer.find('textarea:first')

                        if (targetComponent.length > 0)
                        {
                            this.$actionHandler().copyTextInline(targetComponent[0].id, this.text_);
                            return;
                        }
                    }

                    const cursorPos = targetComponent.prop('selectionStart');
                    const v = targetComponent.val();
                    const textBefore = v.substring(0,  cursorPos);
                    const textAfter  = v.substring(cursorPos, v.length);
    
                    targetComponent.val(textBefore + this.text_ + textAfter);
                    targetComponent.focus();
                    targetComponent.setCursorPosition(cursorPos + this.text_.length);
                }
            },
    
            copyToFocused: function () {
                if (this.focusedElement_ !== null && typeof this.focusedElement_ === 'object') {
                    this.copyToComponent(this.focusedElement_);
                }
            },
    
            getComponentSelector: function (){
                switch (this.targetType_)
                {
                    case 'component':
                        return "#" + this.target_.cid;
                    case 'componentId':
                        return "#" + this.target_;
                    case 'componentName':
                        return "[name = " + this.target_ + "]";
                    case 'componentSelector':
                        return this.target_;
                    default:
                        return "";
                }
            },
        },
        
    }