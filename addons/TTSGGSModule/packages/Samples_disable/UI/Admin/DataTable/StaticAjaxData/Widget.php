<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Widget extends \ModulesGarden\TTSGGSModule\Components\Widget\Widget implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $cid = 'container-xxxx';

    public function loadHtml(): void
    {
        $form = new Form2();

        $this->addElementToToolbar($this->addElement((new ButtonSubmitSuccess())->onClick(new FormSubmit($form))));
        $this->addElement($form);
    }
}