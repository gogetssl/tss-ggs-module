<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonCreate;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;

class UserCreate extends ButtonCreate
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->onClick(new ModalLoad(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserCreate()));
    }
}
