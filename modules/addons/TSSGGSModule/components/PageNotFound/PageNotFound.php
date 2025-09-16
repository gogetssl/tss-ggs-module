<?php

namespace ModulesGarden\TSSGGSModule\Components\PageNotFound;

use ModulesGarden\TSSGGSModule\Components\BlockZeroData\BlockZeroData;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\RedirectToPreviousPage;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;

class PageNotFound extends BlockZeroData implements ClientAreaInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setDescription($this->translate('description'));

        $button = new ButtonPrimary();
        $button->setTitle($this->translate('return_button'));
        $button->onClick(new RedirectToPreviousPage());
        $this->addElement($button);
    }
}
