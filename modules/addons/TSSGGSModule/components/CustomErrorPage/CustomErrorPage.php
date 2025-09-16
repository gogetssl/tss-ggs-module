<?php

namespace ModulesGarden\TSSGGSModule\Components\CustomErrorPage;

use ModulesGarden\TSSGGSModule\Components\BlockZeroData\BlockZeroData;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\RedirectToPreviousPage;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;

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