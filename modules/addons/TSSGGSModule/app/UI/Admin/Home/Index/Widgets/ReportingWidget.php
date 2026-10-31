<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Modals\ReportingModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;

/**
 * Module issue reporting: opens the report modal (activity log, environment details)
 */
class ReportingWidget extends Card
{
    public function loadHtml(): void
    {
        $this->start('file-document-outline', $this->translate('title'));

        $button = new Button();
        $button->setTitle($this->translate('sendReport'));
        $button->onClick(new ModalLoad(new ReportingModal()));

        $body = new Container();
        $body->setCss('tssggs-dcard__body');
        $body->addElement(Card::html('<p class="tssggs-muted">' . Card::e($this->translate('info')) . '</p>'));
        $body->addElement($button);
        $this->addElement($body);
    }
}
