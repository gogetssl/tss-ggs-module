<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonCreate;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;

class UserCreate extends ButtonCreate
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->onClick(new ModalLoad(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserCreate()));
    }
}
