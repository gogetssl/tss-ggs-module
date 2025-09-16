var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'id',
            'css',
            'elements',
            'search',
            'hidePagination',
            'curPage',
            'allPages',
            'pagesMap',
            'recordsPerPageOptions',
            'recordsPerPage',
        ],
        data: function () {
            return {
                css_: [],
                elements_: [],
                search_: '',
                hidePagination_: false,
                curPage: 1,
                allPages: 1,
                pagesMap: [],
                recordsPerPageOptions_: [],
                recordsPerPage: null,
                recordsSet_: [],
                dataShowing: 0,
                dataFrom: 0,
            };
        },
        created: function () {
            if (this.elements_.length == 0)
            {
                this.loadAjaxData_();
            }
        },
        
        methods: {
            onReload: function (data = {}) {
                this.propagateSlotsData_(data.slots ? data.slots : []);
                this.loadAjaxData_();
            },
            
            loadAjaxData_: function () {
                this.massActionIds_ = [];
                this.massActionOpen_ = false;
                
                var self = this;
                var resp = this.loadDataFromServer_({
                    iDisplayLength: this.recordsPerPage,
                    iDisplayStart: this.dataShowing,
                    sSearch: (this.sSearch !== false ? this.sSearch : ''),
                    filters: []
                }, this.ajaxData_).then(function (data) {
                    
                    data = data.data;
                    
                    if (data.status === 'error')
                    {
                        self.noData = true;
                    } else
                    {
                        self.recordsSet_ = data.data.recordsSet;
                        self.sortBy_ = self.recordsSet_.sort.by;
                        self.sortDir_ = self.recordsSet_.sort.dir;
                        
                        data = data.data.recordsSet;
                        
                        if (typeof data.elements == "undefined")
                        {
                            data.elements = [];
                        }
                        
                        self.dataFrom = data.fullDataLength;
                        self.dataShowing = data.offset;
                        self.dataTo = data.elements.length + data.offset;
                        
                        self.noData = data.elements.length <= 0;
                    }
                    self.updatePagination();
                }).catch(function (jqXHR, textStatus, errorThrown) {
                    self.records_ = [];
                    self.noData = true;
                });
            },
            searchData: function (event) {
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
            },
            updatePagination: function () {
                var self = this;
                self.curPage = (parseInt(self.dataShowing) / parseInt(self.recordsPerPage_)) + 1;
                var tempPages = parseInt(self.dataFrom) > parseInt(self.recordsPerPage_) ? parseInt(parseInt(self.dataFrom) / parseInt(self.recordsPerPage_)) : 0;
                self.allPages = parseInt(tempPages + ((parseInt(self.recordsPerPage_) * tempPages) < parseInt(self.dataFrom) ? 1 : 0));
                self.updatePagesMap();
            },
            updatePagesMap: function () {
                var self = this;
                
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
            },
            updateLength: function (length) {
                this.recordsPerPage = length;
                this.dataShowing = 0; //reset offsetu żeby po wejściu w większe strony no ucinało końcówki
                this.loadAjaxData_();
            },
            searchDataEnter: function (event) {
                event.preventDefault();
                this.searchData(event);
            },
            changePage: function (event) {
                var self = this;
                if ($(event.target).parent().hasClass('disabled') === false && $(event.target).hasClass('disabled') === false)
                {
                    var newPageNumber = $(event.target).attr('data-dt-idx');
                    self.dataShowing = ((newPageNumber < 1) ? 0 : newPageNumber - 1) * parseInt(self.recordsPerPage);
                    self.loadAjaxData_();
                }
            },
        }
    }