<?php

namespace ModulesGarden\TTSGGSModule\Components\PageNotFound;

use ModulesGarden\TTSGGSModule\Components\BlockZeroData\BlockZeroData;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\RedirectToPreviousPage;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;

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
