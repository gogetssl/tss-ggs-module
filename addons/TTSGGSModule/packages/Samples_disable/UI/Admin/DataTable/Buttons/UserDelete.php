<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons;

use ModulesGarden\TTSGGSModule\Components\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Action;

class UserDelete extends IconButton\IconButtonDelete
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->onClick(Action::modalOpen(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserDelete()));
    }
}
