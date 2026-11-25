<?php

namespace ModulesGarden\TSSGGSModule\App\Pages\Admin;

use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Kernel\Table;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use WHMCS\Database\Capsule;

/**
 * Admin page "Automation Queue": the work list of SSL AutoInstall (validate, install, reissue tasks) with search,
 * sorting and "Reset Task". Templates: resources/templates/admin/automationQueue.
 */
class AutomationQueue extends Page
{
    const LANG = 'admin.automation_queue.index';

    /** status => colour of its label */
    const STATUS_TONES = ['pending' => 'info', 'processing' => 'warning', 'done' => 'success', 'error' => 'danger', 'cancelled' => 'secondary'];

    public function index()
    {
        $table = $this->table();

        return $this->request->isHtmx()
            ? $this->fragment('admin/partials/table', ['table' => $table])
            : $this->view('admin/automationQueue/index', ['table' => $table, 'showAbout' => !GeneralSettings::enabled('hideInfoMessages')]);
    }

    /** POST id: the task goes back to Pending and the next cron run takes it again */
    public function postReset()
    {
        $this->attempt(function() {
            AutoInstallQueue::resetTask($this->request->int('id'));
        }, $this->t('data_tables.queue.forms.reset_task_form.update_success'));

        return $this->request->isHtmx()
            ? $this->fragment('admin/automationQueue/table', ['table' => $this->table(), 'flash' => $this->takeFlash()])
            : $this->redirect();
    }

    protected function table(): array
    {
        $t     = function($key) {
            return $this->t('data_tables.queue.data_table.' . $key);
        };
        $table = Table::make($this->request, 'queue')
            ->column('id', $t('id'), true, Table::SEARCH_INT)
            ->column('service_id', $t('service'), true, Table::SEARCH_INT)
            ->column('domain', $t('domain'), true, Table::SEARCH_TEXT)
            ->column('type', $t('type'), true, Table::SEARCH_TEXT)
            ->column('status', $t('status'), true, Table::SEARCH_TEXT)
            ->column('attempts', $t('attempts'), true)
            ->column('error_message', $t('message'), false, Table::SEARCH_TEXT)
            ->column('updated_at', $t('updated'), true)
            ->sortBy('id', 'desc')
            ->rowTemplate('admin/automationQueue/row', true)
            ->run(Capsule::table(AutoInstallQueue::queueTable())->select('id', 'client_id', 'service_id', 'domain', 'type', 'status', 'attempts', 'error_message', 'updated_at'));

        foreach($table['rows'] as &$row)
        {
            $row['tone']     = self::STATUS_TONES[$row['status']] ?? 'default';
            $row['label']    = ucfirst((string)$row['status']);
            // a task that waits or runs has nothing to reset
            $row['canReset'] = !in_array($row['status'], ['pending', 'processing'], true);
        }

        return $table;
    }
}
