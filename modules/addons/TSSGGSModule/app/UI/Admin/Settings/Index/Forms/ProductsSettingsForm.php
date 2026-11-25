<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\ProductsSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Settings > Products: how the WHMCS product list follows the provider's catalogue (App\Libs\Products\CatalogueSync). The
 * changes themselves are listed on the Products page, tab "Catalogue changes". Stored in the general settings.
 */
class ProductsSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    use SettingsLayout;

    public function __construct()
    {
        parent::__construct();

        $this->provider       = ProductsSettingsProvider::class;
        $this->providerAction = ProductsSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('ProductsSettingsForm');

        $catalogue = $this->section('catalogueSync', 'sync', null, ['catalogueMode_manual', 'catalogueMode_alterations', 'catalogueMode_alterations_removal', 'catalogueMode_all']);
        $this->builder->addFieldInContainer($catalogue, (new Dropdown())->setName('catalogueSyncMode'), true);
        $this->builder->addFieldInContainer($catalogue, (new Switcher())->setName('catalogueChangesEmail')->addClass('switcher-revert'), true);

        $this->saveBar();
    }
}
