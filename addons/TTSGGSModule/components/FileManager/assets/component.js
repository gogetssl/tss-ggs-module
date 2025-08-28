var component = {
    extends: BaseDataComponent, template: '#template-name#', props: [
        'title',
        'columns',
        'records',
        'actions',
        'search',
        'autoload_data_after_created',
        'start_length',
        'id',
        'has_mass_actions',
        'is_sortable',
        'elements',
        'infinity',
        'data',
        'recordsPerPageOptions',
        'recordsPerPage',
        'hidePagination',
        'uniqueColumnName',
        'draggable',
        'reorderProviderAction',
    ],
    data: function () {
        return {
            title_: '',
            dataRows: [],
            recordsPerPage_: null,
            addTimeout: false,
            sSearch: false,
            sSearchPrevious: false,
            dataShowing: 0,
            dataTo: 0,
            dataFrom: 0,
            curPage: 1,
            allPages: 1,
            pagesMap: [],
            loading: false,
            show: true,
            showModal: false,
            noData: false,
            searchEnabled: true, //new
            recordsPerPageOptions_: [],
            massActionOpen_: false,
            massActionIds_: [],

            elements_: [],
            columns_: [],
            data_: [],
            records_: [],
            search_: null,
            sortDir_: '',
            sortBy_: '',
            recordsSet_: [],
            selectedMassRows_: [],
            hidePagination_: false,
            uniqueColumnName_: 'id',
            draggable_: false,
            reorderProviderAction_: 'update',
            breadcrumbs_: []
        };
    },
    created: function () {
        if (this.records_.length == 0)
        {
            this.loadAjaxData_();
        }
    },
    updated: function () {
        var event = new Event('dataTableUpdate', {
            bubbles: true,
            cancelable: true
        });

        document.dispatchEvent(event);
    },
    methods: {
        reloadParent: function () {
            this.loadAjaxData_();
        },
        onReload: function (data = {}) {
            this.propagateSlotsData_(data.slots ? data.slots : []);
            this.loadAjaxData_();
        },
        runAjaxOnAction_: function (data) {
            return this.loadAjaxData_();
        },
        loadAjaxData_: function () {
            this.massActionIds_ = [];
            this.massActionOpen_ = false;

            var self = this;
            var resp = this.loadDataFromServer_({
                iDisplayLength: this.recordsPerPage_,
                iDisplayStart: this.dataShowing,
                sSearch: (this.sSearch !== false ? this.sSearch : ''),
                sortBy: this.sortBy_,
                sortDir: this.sortDir_,
                filters: []
            }, this.ajaxData_).then(function (data) {

                data = data.data;

                if (data.status === 'error')
                {
                    self.noData = true;
                } else
                {
                    var actionData = data.data;

                    self.recordsSet_ = data.data.recordsSet;
                    self.sortBy_ = self.recordsSet_.sort.by;
                    self.sortDir_ = self.recordsSet_.sort.dir;

                    data = data.data.recordsSet;

                    if (typeof data.records == "undefined")
                    {
                        data.records = [];
                    }


                    if (data.records.length === 0 && self.dataShowing > 0)
                    {
                        self.dataShowing = 0;
                        return self.loadAjaxData_();
                    }


                    self.records_ = data.records;
                    self.dataShowing = data.offset;
                    self.dataTo = data.records.length + data.offset;
                    self.dataFrom = data.fullDataLength;
                    self.addTimeout = false;
                    if (self.addTimeout === true)
                    {
                        setTimeout(self.loadAjaxData_, 60000);
                        self.addTimeout = false;
                    }

                    self.noData = data.records.length > 0 ? false : true;
                }
                self.updatePagination();
            }).catch(function (jqXHR, textStatus, errorThrown) {
                self.records_ = [];
                self.noData = true;
                //mgEventHandler.runCallback('DatatableDataLoadingFailed', self.id, {jqXHR: jqXHR, textStatus: textStatus, errorThrown: errorThrown});
            });
        },
        updateLength: function (length) {
            this.recordsPerPage_ = length;
            this.dataShowing = 0; //reset offsetu żeby po wejściu w większe strony no ucinało końcówki
            this.loadAjaxData_();
        }, searchDataEnter: function (event) {
            event.preventDefault();
            this.searchData(event);
        }, searchData: function (event) {
            var self = this;
            self.sSearch = $(event.target).val() === '' ? false : $(event.target).val();
            if (self.sSearch !== false)
            {
                if (self.sSearchPrevious !== false && self.sSearchPrevious !== self.sSearch)
                {
                    self.dataShowing = 0;
                }
                self.sSearchPrevious = self.sSearch;
            } else
            {
                self.sSearchPrevious = false;
            }
            self.loadAjaxData_();
        }, updatePagination: function () {
            var self = this;
            self.curPage = (parseInt(self.dataShowing) / parseInt(self.recordsPerPage_)) + 1;
            var tempPages = parseInt(self.dataFrom) > parseInt(self.recordsPerPage_) ? parseInt(parseInt(self.dataFrom) / parseInt(self.recordsPerPage_)) : 0;
            self.allPages = parseInt(tempPages + ((parseInt(self.recordsPerPage_) * tempPages) < parseInt(self.dataFrom) ? 1 : 0));
            self.updatePagesMap();
        }, updatePagesMap: function () {
            var self = this;

            self.pagesMap = [];

            if (self.allPages <= 0)
            {
                self.pagesMap[1] = 1;
                return;
            }

            if (self.allPages <= 7)
            {
                self.pagesMap = [];
                for (i = 1; i <= self.allPages; i++)
                {
                    self.pagesMap[i] = i;
                }

                return;
            }

            self.pagesMap = [
                self.curPage - 5, self.curPage - 4, self.curPage - 3, self.curPage - 2, self.curPage - 1, self.curPage, self.curPage + 1, self.curPage + 2, self.curPage + 3, self.curPage + 4, self.curPage + 5
            ];
            for (i = 0; i < self.pagesMap.length; i++)
            {
                if (self.pagesMap[i] < 0 || self.pagesMap[i] > self.allPages)
                {
                    self.pagesMap[i] = null;
                }
            }

            if (self.pagesMap.indexOf(self.allPages) === -1)
            {
                self.pagesMap[self.pagesMap.length - 1] = self.allPages;
            }
            if (self.pagesMap.indexOf(1) === -1)
            {
                self.pagesMap[0] = 1;
            }

            if (self.allPages > 7 && self.curPage > 4)
            {
                self.pagesMap[self.pagesMap.indexOf(Math.min(self.curPage - 1 > 1 ? self.curPage - 1 : self.curPage, self.curPage - 2 > 1 ? self.curPage - 2 : self.curPage, self.curPage - 3 > 1 ? self.curPage - 3 : self.curPage, self.curPage - 4 > 1 ? self.curPage - 4 : self.curPage))] = '...';
            }

            for (i = self.pagesMap.length - 1; i > self.pagesMap.indexOf(self.curPage); i--)
            {
                if (i === 8 && self.pagesMap[i] === self.curPage + 3 && self.pagesMap[i] !== self.allPages)
                {
                    self.pagesMap[i] = null;
                }
            }

            if (self.allPages > 7 && (4 <= self.allPages - self.curPage))
            {
                self.pagesMap[self.pagesMap.indexOf(self.allPages) - 1] = '...';
            }
        }, changePage: function (event) {
            var self = this;
            if ($(event.target).parent().hasClass('disabled') === false && $(event.target).hasClass('disabled') === false)
            {
                var newPageNumber = $(event.target).attr('data-dt-idx');
                self.dataShowing = ((newPageNumber < 1) ? 0 : newPageNumber - 1) * parseInt(self.recordsPerPage_);
                self.loadAjaxData_();
            }
        }, /*
         SORTING
         */
        updateSorting: function (event, column) {
            if (!column.sortable)
            {
                return;
            }

            if (column.name == this.sortBy_)
            {
                this.sortDir_ = this.sortDir_ == 'ASC' ? 'DESC' : 'ASC';
            } else
            {
                this.sortBy_ = column.name;
                this.sortDir_ = 'ASC';
            }

            this.loadAjaxData_();
        },
        massActionCheckbox: function (value) {
            this.massActionIds_ = [];
            if (this.massActionOpen_)
            {
                this.records_.map(val => this.massActionIds_.push(val[this.uniqueColumnName_]))
            }
        },
        getSortedClass(column)
        {
            if (!column.sortable)
            {
                return null;
            }

            var sortClass = 'sorting';

            if (this.sortBy_ === column.name)
            {
                sortClass += ' sorting_' + (this.sortDir_).toLowerCase();
            }

            return sortClass;
        },
        onDropRow: function (value) {
            setTimeout(function () {
                this.loadDataFromServer_({
                        formData: {
                            records: this.records_.map(val => val[this.uniqueColumnName_])
                        },
                        providerAction: this.reorderProviderAction_
                    }, this.ajaxData_
                ).then(function () {
                });
            }.bind(this), 300);
        },
    },
    watch: {
        massActionIds_: {
            deep: true,
            handler: function () {
                if(!this.massActionIds_.length){
                    this.massActionOpen_ = false;
                }
            }
        },
    }
}