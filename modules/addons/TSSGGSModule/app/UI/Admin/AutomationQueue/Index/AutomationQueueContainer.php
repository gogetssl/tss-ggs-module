<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\DataTable;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class AutomationQueueContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        if(!\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('hideInfoMessages'))
        {
            $text = new Text();
            $text->setCss('tssggs-html');
            $text->setText($this->explanation());
            $this->addElement($text);
        }

        $this->addElement(new DataTable());
    }

    protected function explanation(): string
    {
        $t    = function($key) { return htmlspecialchars((string)$this->translate($key), ENT_QUOTES); };
        $item = function($key) use ($t) { return '<li>' . $t($key) . '</li>'; };

        return '<div class="tssggs-explain"><span class="tssggs-explain__icon">' . Icons::svg('information-outline') . '</span><div>'
            . '<h3>' . $t('about_title') . '</h3>'
            . '<p>' . $t('about_intro') . '</p>'
            . '<ul>' . $item('about_validate') . $item('about_install') . $item('about_reissue') . $item('about_status') . $item('about_error') . '</ul>'
            . '</div></div>';
    }
}
