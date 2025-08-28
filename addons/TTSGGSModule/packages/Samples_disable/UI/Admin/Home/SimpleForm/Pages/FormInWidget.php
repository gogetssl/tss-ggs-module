<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Pages;

use ModulesGarden\TTSGGSModule\Components\DropdownMenu\DropdownMenu;
use ModulesGarden\TTSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserDelete;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Forms\SimpleAbstractForm;

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
