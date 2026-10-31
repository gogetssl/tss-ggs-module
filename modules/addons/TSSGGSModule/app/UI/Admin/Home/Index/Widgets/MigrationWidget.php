<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\App\Migration\Store;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

/**
 * Dashboard card: legacy modules found, last migration, backup expiry. Links to the Migration page.
 */
class MigrationWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('database-arrow-right');

        $lines = [];
        $found = Detector::detect();
        foreach($found as $source => $info)
        {
            $choice  = Choice::get($source);
            $lines[] = '<b>' . htmlspecialchars($info['label']) . '</b> ' . htmlspecialchars((string)$info['version']) . ' — '
                . htmlspecialchars($this->translate('services', ['n' => (int)($info['counts']['services']['Active'] ?? 0)]))
                . ($choice ? ' · ' . htmlspecialchars($this->translate('choice_' . $choice)) : '');
        }

        $run = Store::latestRun();
        if($run)
        {
            $counts  = Store::jobCounts((int)$run['id']);
            $lines[] = htmlspecialchars($this->translate('last_run', ['id' => (int)$run['id'], 'status' => $run['status'], 'done' => $counts['done'], 'failed' => $counts['failed'], 'skipped' => $counts['skipped']]));
            if($run['backup_prefix'] && $run['backup_expires_at'])
            {
                $lines[] = htmlspecialchars($this->translate('backups_until', ['date' => substr($run['backup_expires_at'], 0, 10)]));
            }
        }

        if(!$lines)
        {
            $lines[] = htmlspecialchars($this->translate('nothing'));
        }

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText('<div class="tssggs-migration-widget"><p>' . implode('</p><p>', $lines) . '</p>'
            . '<a class="lu-btn lu-btn--default" href="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=index">' . htmlspecialchars($this->translate('open')) . '</a></div>');
        $this->addElement($text);
    }
}
