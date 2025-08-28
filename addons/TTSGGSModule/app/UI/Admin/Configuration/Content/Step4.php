<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;

class Step4 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        $this->setTitle($this->translate('step4_title'));
        $this->addElement(new ApiSettingsForm(true));

    }
}