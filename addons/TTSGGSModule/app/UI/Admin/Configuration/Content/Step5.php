<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider\DataTable;

class Step5 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        $this->setTitle($this->translate('step5_title'));

        $this->addElement(new DataTable());

        $nextStep = new ButtonSuccess();
        $nextStep->setCss('lu-btn lu-btn--success mt30_custom');
        $nextStep->onClick(Action::redirect('addonmodules.php?module=TTSGGSModule&mg-page=configuration&mg-action=step6'));
        $nextStep->setTitle($this->translate('next_step_btn'));
        $this->addElement($nextStep);
    }
}