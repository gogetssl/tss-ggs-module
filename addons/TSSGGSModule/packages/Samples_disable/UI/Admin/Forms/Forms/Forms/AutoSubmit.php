<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxAutoSubmitInterface;

class AutoSubmit extends AbstractForm /*implements AjaxAutoSubmitInterface*/
{
    public function loadHtml(): void
    {
        $this->setAction('https://google.com');
        $this->setMethod('post');
        $this->setTarget('_blank');
    }
}
