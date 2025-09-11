<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms\CurrencyForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class CurrencyWidget extends Widget
{
    public function loadHtml(): void
    {
        //$this->setTitle($this->translate('title'));
        $this->addElement(new CurrencyForm());
    }
}