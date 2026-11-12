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
    const RUN_ICONS = ['completed' => 'check', 'failed' => 'close', 'rolled_back' => 'undo'];

    /** the panel is removed this many days after a completed migration */
    const DAYS = 14;

    public static function relevant(): bool
    {
        try
        {
            $run = Store::latestRun();
            if($run && self::removedAt($run) !== null && time() >= self::removedAt($run))
            {
                return false;
            }

            return Detector::anyFound() || $run;
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    /** Timestamp the panel disappears: 14 days after the run finished; null while it is not completed */
    protected static function removedAt(array $run)
    {
        if($run['status'] !== 'completed' || empty($run['finished_at']))
        {
            return null;
        }

        return strtotime((string)$run['finished_at']) + self::DAYS * 86400;
    }

    public function loadHtml(): void
    {
        $run   = Store::latestRun();
        $aside = $run ? Card::status(self::RUN_TONES[$run['status']] ?? 'warn', $this->translate('run_short', ['id' => (int)$run['id'], 'status' => $run['status']]),
            self::RUN_ICONS[$run['status']] ?? 'progress-clock') : '';
        $this->start('database-arrow-right', $this->translate('title'), $aside);

        $html = '';
        foreach(Detector::detect() as $source => $info)
        {
            $choice = Choice::get($source);
            $html  .= Card::row('package-variant-closed', $info['label'] . ' ' . $info['version'], $choice ? $this->translate('choice_' . $choice) : '',
                '<b>' . Card::e($this->translate('services', ['n' => (int)($info['counts']['services']['Active'] ?? 0)])) . '</b>');
        }

        if($run)
        {
            $counts = Store::jobCounts((int)$run['id']);
            $html  .= '<p class="tssggs-dcard__sub">' . Card::icon('check-circle-outline')
                . Card::e($this->translate('last_run_counts', ['done' => $counts['done'], 'failed' => $counts['failed'], 'skipped' => $counts['skipped']])
                    . ($run['backup_prefix'] && $run['backup_expires_at'] ? '. ' . $this->translate('backups_until', ['date' => substr($run['backup_expires_at'], 0, 10)]) : ''))
                . '</p>';
            if(self::removedAt($run) !== null)
            {
                $html .= '<p class="tssggs-dcard__sub">' . Card::icon('clock-outline')
                    . Card::e($this->translate('removed_on', ['date' => date('Y-m-d', self::removedAt($run)), 'days' => self::DAYS])) . '</p>';
            }
        }
        $this->addElement(Card::html('<div class="tssggs-dcard__rows">' . $html . '</div>'));

        $this->foot('addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=index', 'database-outline', $this->translate('open'));
    }
}
