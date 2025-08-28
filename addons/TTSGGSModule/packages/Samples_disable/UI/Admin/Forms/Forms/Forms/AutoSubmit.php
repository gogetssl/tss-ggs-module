<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxAutoSubmitInterface;

class AutoSubmit extends AbstractForm /*implements AjaxAutoSubmitInterface*/
{
    public function loadHtml(): void
    {
        $this->setAction('https://google.com');
        $this->setMethod('post');
        $this->setTarget('_blank');
    }
}
