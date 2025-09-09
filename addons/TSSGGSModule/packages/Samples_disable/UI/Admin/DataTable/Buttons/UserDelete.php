<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons;

use ModulesGarden\TSSGGSModule\Components\IconButton;
use ModulesGarden\TSSGGSModule\Core\Components\Action;

class UserDelete extends IconButton\IconButtonDelete
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->onClick(Action::modalOpen(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserDelete()));
    }
}
