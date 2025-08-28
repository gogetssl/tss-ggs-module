<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Tabs;

use ModulesGarden\TTSGGSModule\Components\Badge\BadgeDanger;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeInfo;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeSuccess;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeWarning;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Tabs\Tabs\Tab;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Forms\SimpleAbstractForm;

class Tabs extends \ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget implements AdminAreaInterface, AjaxOnLoadInterface
{
    public function loadData(): void
    {
        $tab2 = new \ModulesGarden\TTSGGSModule\Components\Tab\Tab();
        $tab2->setTitle('YYYYYYYY');
        $tab2->setContent('2QWWQWQWQWWWQW3');

        $customTab = new Tab();
        $customTab->setId('dupa-123');

        $this->addTab($tab2);
        $this->addTab($customTab);
    }

    public function loadHtml(): void
    {
        $tab = new \ModulesGarden\TTSGGSModule\Components\Tab\Tab();
        $tab->addElement($this->form());
        $tab->setTitle('Form');
        $this->addTab($tab);

        $tab = new \ModulesGarden\TTSGGSModule\Components\Tab\Tab();
        $tab->addElement($this->badges());
        $tab->setTitle('Badges');
        $this->addTab($tab);

        for ($i = 0; $i < 20; $i++)
        {
            $tab = new \ModulesGarden\TTSGGSModule\Components\Tab\Tab();
            $tab->setTitle($i);
            $tab->setContent($i);
            $this->addTab($tab);
        }
    }

    protected function form()
    {
        return new SimpleAbstractForm();
    }

    protected function badges()
    {
        $bar = new Toolbar();

        $bar->addElement((new BadgeDanger())->setText('1'));
        $bar->addElement((new BadgeSuccess())->setText('2'));
        $bar->addElement((new BadgeWarning())->setText('3'));
        $bar->addElement((new BadgeInfo())->setText('4'));

        return $bar;
    }
}
