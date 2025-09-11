<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Pages;

use ModulesGarden\TSSGGSModule\Components\DropdownMenu\DropdownMenu;
use ModulesGarden\TSSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserDelete;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Forms\SimpleAbstractForm;

class FormInWidget extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Form in widget');
        $this->addElement(new SimpleAbstractForm());

        $this->addToToolbar($this->createDropdown());
    }

    public function createDropdown()
    {
        return (new DropdownMenu())
            ->setName('dropdownmenu')
            ->addItem((new DropdownMenuItem())
                ->setIcon('delete')
                ->setTitle('XXXXXX')
                ->setType(Color::DANGER)
                ->onClick(Action::modalLoad(new UserDelete()))
            );
    }
}
