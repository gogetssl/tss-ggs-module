<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Providers\VendorSelectionProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\ConfigurationVendorSelect\ConfigurationVendorSelect;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;


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
            $CONFIG['SystemURL'].'/modules/addons/TSSGGSModule/resources/assets/img/sslstore.png',
            $CONFIG['SystemURL'].'/modules/addons/TSSGGSModule/resources/assets/img/gogetssl.png'
        ]);
        $this->addElement($selectedVendor);
        $this->addElement($toolbar);
    }
}