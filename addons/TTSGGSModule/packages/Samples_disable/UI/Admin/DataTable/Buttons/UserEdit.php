<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons;

use ModulesGarden\TTSGGSModule\Components\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;

class UserEdit extends IconButton\IconButtonEdit
{
    public function loadHtml(): void
    {
        $this->onClick(new ModalLoad(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserEdit()));
    }
}
