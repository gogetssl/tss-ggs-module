<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Providers\VendorSelectionProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\ConfigurationVendorSelect\ConfigurationVendorSelect;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;


class VendorSelectionForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = VendorSelectionProvider::class;
        $this->providerAction = VendorSelectionProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        global $CONFIG;

        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('VendorSelectionForm');
        $toolbar = new Toolbar();

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($this->translate('save'))
                ->onClick(new FormSubmit($this))
        );

        $selectedVendor = new ConfigurationVendorSelect();
        $selectedVendor->setOptions([
            $CONFIG['SystemURL'].'/modules/addons/TTSGGSModule/resources/assets/img/sslstore.png',
            $CONFIG['SystemURL'].'/modules/addons/TTSGGSModule/resources/assets/img/gogetssl.png'
        ]);
        $this->addElement($selectedVendor);
        $this->addElement($toolbar);
    }
}