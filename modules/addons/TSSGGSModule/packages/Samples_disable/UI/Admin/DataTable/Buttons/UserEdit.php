<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons;

use ModulesGarden\TSSGGSModule\Components\IconButton;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;

class UserEdit extends IconButton\IconButtonEdit
{
    public function loadHtml(): void
    {
        $this->onClick(new ModalLoad(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserEdit()));
    }
}
