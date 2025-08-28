<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Modals\ReportingModal;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;

class ReportingWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('alert-outline');
        $this->setSlot('customcontentclass', 'report-issue');

        $text = new Text();
        $text->setText($this->translate('info'));
        $this->addElement($text);

        $this->addElement((new Text())->setText('<br><br>'));


        $button = new ButtonPrimary();
        $button->setTitle($this->translate('sendReport'));
        $button->onClick(new ModalLoad(new ReportingModal()));
        $this->addElement($button);
    }
}