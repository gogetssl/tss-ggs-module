<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Widget extends \ModulesGarden\TSSGGSModule\Components\Widget\Widget implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $cid = 'container-xxxx';

    public function loadHtml(): void
    {
        $form = new Form2();

        $this->addElementToToolbar($this->addElement((new ButtonSubmitSuccess())->onClick(new FormSubmit($form))));
        $this->addElement($form);
    }
}