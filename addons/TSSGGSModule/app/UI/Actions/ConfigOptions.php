<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Actions;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Row\Row;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class ConfigOptions extends \ModulesGarden\TSSGGSModule\Packages\Product\UI\Forms\ProductConfiguration
{
    public function loadHtml(): void
    {
        $this->generalSection();
    }

    protected function generalSection()
    {
        $row = new Row();

        $generalSection2 = new Widget();
        $generalSection2->setTitle('title');
        $generalSection2->addElement($row);

        $this->builder->addFieldInContainer($row, (new Dropdown())->setName('customconfigoption[productType]')
            ->setOptions(['Opcja1', 'Opcja2', 'Opcja3'])
            ->setMultiple());

        $this->builder->addFieldInContainer($row, (new Dropdown())->setName('customconfigoption[virtualizationType]')->setDefaultValueAsFirstOption());
        $this->builder->addFieldInContainer($row, (new Dropdown())->setName('customconfigoption[storageType]')->setDefaultValueAsFirstOption());
        $this->builder->addFieldInContainer($row, (new Dropdown())->setName('customconfigoption[imageFormat]')->setDefaultValueAsFirstOption());
        $this->builder->addFieldInContainer($row, (new Dropdown())->setName('customconfigoption[location]')->setDefaultValueAsFirstOption());
        $this->builder->addFieldInContainer($row, (new Dropdown())->setName('customconfigoption[computeResource]')->setDefaultValueAsFirstOption());

        $this->builder->addFieldInContainer($row, (new Switcher())->setName('customconfigoption[switchers][0]'));
        $this->builder->addFieldInContainer($row, (new Switcher())->setName('customconfigoption[switchers][1]'));
        $this->builder->addFieldInContainer($row, (new Switcher())->setName('customconfigoption[switchers][2]'));

        $this->builder->addFieldInContainer($row, (new FormInputText())->setName('customconfigoption[project]'));
        $this->builder->addElement($generalSection2);
    }
}