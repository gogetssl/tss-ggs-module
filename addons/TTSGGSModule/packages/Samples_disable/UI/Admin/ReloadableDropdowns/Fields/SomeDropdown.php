<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ReloadableDropdowns\Fields;

use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class SomeDropdown extends Dropdown implements AjaxComponentInterface, AdminAreaInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('slaveDropdown');
        $this->setName('slaveDropdown');
    }

    public function loadHtml(): void
    {
        $masterValue = Request::get('ajaxData')['value'];

        $this->setDefaultValueAsFirstOption();
        $this->setOptions($this->getOptionsByMasterValue($masterValue));
    }

    public function getOptionsByMasterValue($masterValue): array
    {
        switch ($masterValue)
        {
            case '2':
                return [
                    'x' => "Opcja X z zestawu 2",
                    'y' => "Opcja Y z zestawu 2",
                    'z' => "Opcja Z z zestawu 2",
                ];
            case '3':
                return [
                    'x' => "Opcja X z zestawu 3",
                    'y' => "Opcja Y z zestawu 3",
                    'z' => "Opcja Z z zestawu 3",
                ];
            default:
                return [
                    'x' => "Opcja X z zestawu 1",
                    'y' => "Opcja Y z zestawu 1",
                    'z' => "Opcja Z z zestawu 1",
                ];
        }
    }
}