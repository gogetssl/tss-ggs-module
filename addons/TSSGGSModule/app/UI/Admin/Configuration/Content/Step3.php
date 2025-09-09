<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Forms\VendorSelectionForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

class Step3 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        $this->setTitle($this->translate('step3_title'));
        $this->addElement(new VendorSelectionForm());

    }
}