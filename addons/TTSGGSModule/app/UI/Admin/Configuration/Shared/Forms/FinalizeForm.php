<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Providers\FinalizeProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;


class FinalizeForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = FinalizeProvider::class;
        $this->providerAction = FinalizeProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $toolbar = new Toolbar();
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($this->translate('finalize'))
                ->onClick(new FormSubmit($this))
        );
        $this->addElement($toolbar);
    }
}