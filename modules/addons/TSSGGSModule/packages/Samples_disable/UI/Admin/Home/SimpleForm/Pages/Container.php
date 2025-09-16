<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Pages;

use ModulesGarden\TSSGGSModule\Components\TextShowHide\TextShowHide;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Forms\SimpleAbstractForm;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new SimpleAbstractForm());
        $this->addElement(new TextShowHide());
    }

    protected function tabs()
    {
//        $tab = new Tab();
//        $tab->setTitle('XXXXX');
//        $tab->addElement(new DataTable());
//        //  $tab->setContent('QQWQWQWQWQW');
//
//        $tab2 = new Tab();
//        $tab2->setTitle('1212121212');
//        $tab2->setContent('QQWQWwwewweweweQWQWQW');
//
//        $tabs = new TabsWidget();
//        $tabs->addTab($tab);
//        $tabs->addTab($tab2);
//
//        return $tabs;
    }
}
