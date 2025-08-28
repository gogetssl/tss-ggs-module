<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ReloadableDropdowns\Forms;

use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ReloadableDropdowns\Fields\SomeDropdown;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ReloadableDropdowns\Providers\ReloadableDropdownsProvider;

class ReloadableDropdownsForm extends AbstractForm implements AdminAreaInterface
{
    protected string $provider = ReloadableDropdownsProvider::class;
    protected string $providerAction = ReloadableDropdownsProvider::ACTION_UPDATE;

    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::twoColumns($this);
    }

    public function loadHtml(): void
    {
        $this->builder->createField(Dropdown::class, 'masterDropdown')
            ->setDefaultValueAsFirstOption()
            ->onChange(new PassAjaxData('slaveDropdown'))
            ->onChange(new ReloadById('slaveDropdown'));
        $this->builder->addElement((new Row()));
        $this->builder->createField(SomeDropdown::class, 'slaveDropdown');
    }
}