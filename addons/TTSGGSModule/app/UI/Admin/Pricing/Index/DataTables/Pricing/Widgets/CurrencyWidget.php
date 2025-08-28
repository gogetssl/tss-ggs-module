<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms\CurrencyForm;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;

class CurrencyWidget extends Widget
{
    public function loadHtml(): void
    {
        //$this->setTitle($this->translate('title'));
        $this->addElement(new CurrencyForm());
    }
}