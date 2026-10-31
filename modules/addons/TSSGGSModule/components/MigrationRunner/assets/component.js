var component =
    {
        extends: BaseDataComponent,
        template: '#template-name#',
        props: [
            'runId',
            'batchUrl',
            'token',
            'initial',
            'labels',
        ],
        data: function () {
            return {
                runId_: 0,
                batchUrl_: '',
                token_: '',
                initial_: null,
                labels_: {},
                progress_: { status: '', percent: 0, jobs: { done: 0, skipped: 0, failed: 0, pending: 0 }, finished: false, error: '' },
                error_: '',
                running: false,
                stopped: false,
            }
        },
        created: function () {
            if (this.initial) {
                this.progress_ = this.initial;
            }
        },
        mounted: function () {
            if (this.progress_ && !this.progress_.finished) {
                this.running = true;
                this.step();
            }
        },
        beforeDestroy: function () {
            this.stopped = true;
        },
        computed: {
            stateLabel: function () {
                var map = this.labels_.states || {};
                return map[this.progress_.status] || this.progress_.status;
            },
            reloadHref: function () {
                return window.location.href;
            },
        },
        methods: {
            step: function () {
                var self = this;
                if (this.stopped) {
                    return;
                }
                var form = new FormData();
                form.append('id', this.runId_);
                form.append('token', this.token_);
                axios.post(this.batchUrl_, form).then(function (response) {
                    var data = response.data || {};
                    if (data.status !== 'success') {
                        self.error_ = data.message || 'Request failed';
                        self.running = false;
                        return;
                    }
                    self.progress_ = data.progress;
                    self.error_ = data.progress.error || '';
                    if (data.progress.finished) {
                        self.running = false;
                        return;
                    }
                    // a short pause keeps the admin session and the server responsive between batches
                    setTimeout(self.step, 400);
                }).catch(function (err) {
                    self.error_ = (err && err.message) ? err.message : 'Request failed';
                    self.running = false;
                });
            },
        },
    }
