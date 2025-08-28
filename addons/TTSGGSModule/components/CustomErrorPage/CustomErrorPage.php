<?php

namespace ModulesGarden\TTSGGSModule\Components\CustomErrorPage;

use ModulesGarden\TTSGGSModule\Components\BlockZeroData\BlockZeroData;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\RedirectToPreviousPage;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;

class CustomErrorPage extends BlockZeroData implements ClientAreaInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->getSlot('title', $this->translate('title')));
        $this->setDescription($this->getSlot('description',$this->translate('description')));

        $button = new ButtonPrimary();
        $button->setTitle($this->translate('return_button'));
        $button->onClick(new RedirectToPreviousPage());
        $this->addElement($button);
    }
}