/**
 * Migration page: the runner of a migration run (template admin/migration/run.tpl, Alpine x-data).
 * While the run is not finished it posts "batch" again and again; each answer brings the progress
 * ({ status, percent, jobs: { done, skipped, failed, pending }, finished, error }). A short pause between the
 * batches keeps the admin session and the server responsive. Leaving the page stops the loop; the run goes on
 * when the page is opened again.
 */
function moduleMigrationRunner(config) {
    'use strict';

    return {
        id: config.id,
        batchUrl: config.batchUrl,
        states: config.states || {},
        progress: config.progress,
        error: config.progress.error || '',
        running: false,

        init: function () {
            if (!this.progress.finished) {
                this.running = true;
                this.step();
            }
        },

        step: function () {
            var self = this;
            var form = new FormData();
            var root = document.getElementById('layers2');
            form.append('id', this.id);
            form.append('token', root ? root.getAttribute('data-token') : '');

            fetch(this.batchUrl, { method: 'POST', body: form, credentials: 'same-origin' })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!data || data.status !== 'success') {
                        self.error = (data && data.message) || 'Request failed';
                        self.running = false;
                        return;
                    }
                    self.progress = data.progress;
                    self.error = data.progress.error || '';
                    if (data.progress.finished) {
                        self.running = false;
                        return;
                    }
                    setTimeout(function () { self.step(); }, 400);
                })
                .catch(function (err) {
                    self.error = (err && err.message) ? err.message : 'Request failed';
                    self.running = false;
                });
        }
    };
}
