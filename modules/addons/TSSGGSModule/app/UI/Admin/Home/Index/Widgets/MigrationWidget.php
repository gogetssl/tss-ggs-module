<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\App\Migration\Store;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Legacy modules found, last migration run, backup expiry. Links to the Migration page.
 * Shown only when a legacy module is installed or a migration has run.
 */
class MigrationWidget extends Card
{
    const RUN_TONES = ['completed' => 'ok', 'failed' => 'bad', 'rolled_back' => 'muted'];

    public static function relevant(): bool
    {
        try
        {
            return Detector::anyFound() || Store::latestRun();
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    public function loadHtml(): void
    {
        $this->start('database-arrow-right', $this->translate('title'));

        $html = '<p class="tssggs-muted">' . Card::e($this->translate('intro')) . '</p><dl class="tssggs-rows">';
        foreach(Detector::detect() as $source => $info)
        {
            $choice = Choice::get($source);
            $html  .= '<div><dt>' . Card::e($info['label']) . ' <span class="tssggs-muted">' . Card::e((string)$info['version']) . '</span></dt>'
                . '<dd>' . Card::e($this->translate('services', ['n' => (int)($info['counts']['services']['Active'] ?? 0)]))
                . ($choice ? '<small>' . Card::e($this->translate('choice_' . $choice)) . '</small>' : '') . '</dd></div>';
        }
        $html .= '</dl>';

        $run = Store::latestRun();
        if($run)
        {
            $counts = Store::jobCounts((int)$run['id']);
            $html  .= '<div class="tssggs-dcard__status">'
                . Card::status(self::RUN_TONES[$run['status']] ?? 'warn', $this->translate('last_run', ['id' => (int)$run['id'], 'status' => $run['status']]))
                . '<p>' . Card::e($this->translate('last_run_counts', ['done' => $counts['done'], 'failed' => $counts['failed'], 'skipped' => $counts['skipped']])) . '</p>'
                . ($run['backup_prefix'] && $run['backup_expires_at'] ? '<p>' . Card::e($this->translate('backups_until', ['date' => substr($run['backup_expires_at'], 0, 10)])) . '</p>' : '')
                . '</div>';
        }

        $this->body($html . Card::link('addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=index', $this->translate('open'), 'is-block'));
    }
}
