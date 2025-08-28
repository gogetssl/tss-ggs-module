const VisibilityMixin = {
    props: [
        'autoHideFieldName',
        'autoHideFieldValue',
        'autoDisableFieldName',
        'autoDisableFieldValue'
    ],
    data: function () {
        return {
            autoHideFieldName_: '',
            autoHideFieldValue_: '',
            autoDisableFieldName_: '',
            autoDisableFieldValue_: ''
        };
    },
    computed: {
        hiddenClass: function () {
            if (this.autoHideFieldName_ && this.data_[this.autoHideFieldName_] == this.autoHideFieldValue_)
            {
                return 'hidden';
            }
            
            return '';
        },
        disabledClass: function () {
            if (this.autoDisableFieldName_ && this.data_[this.autoDisableFieldName_] == this.autoDisableFieldValue_)
            {
                return 'disabled';
            }
            
            return '';
        },
    },
}