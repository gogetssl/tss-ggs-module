<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\ProductsSettingsForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class ProductsSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('package-variant-closed');
        $this->setSubtitle($this->translate('subtitle'));
        $this->addElement(new ProductsSettingsForm());
    }
}
