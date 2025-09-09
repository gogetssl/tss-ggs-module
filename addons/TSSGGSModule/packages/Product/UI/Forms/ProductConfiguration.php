<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\UI\Forms;

use ModulesGarden\TSSGGSModule\App\Http\Actions\MetaData;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\ContainerContentCentered;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Row\Row;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TSSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Exceptions\UserException;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Support\Arr;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\ServersGroups;
use ModulesGarden\TSSGGSModule\Packages\Product\Enums\ConfigSettings;
use ModulesGarden\TSSGGSModule\Packages\Product\Helpers\ProductConfiguration as ProductConfigurationHelper;
use ModulesGarden\TSSGGSModule\Packages\Product\Libs\ConfigurableOptionsGroups\ConfigurableOptionsGroup;
use ModulesGarden\TSSGGSModule\Packages\Product\UI\Formatters\ConfigOptionFullNameFormatter;
use ModulesGarden\TSSGGSModule\Packages\Product\UI\Modals\CreateConfigurableOptions;

class ProductConfiguration extends \ModulesGarden\TSSGGSModule\Components\Form\AbstractForm implements AdminAreaInterface
{
    use TranslatorTrait;

    protected string $provider = \ModulesGarden\TSSGGSModule\Packages\Product\UI\Providers\ProductConfiguration::class;

    public function preLoadHtml(): void
    {
        $this->checkServerRequirements();

        $this->builder = BuilderCreator::twoColumns($this);
        $this->setContainerTag('div');

        parent::preLoadHtml();
    }


    private function checkServerRequirements(): void
    {
        if (!Arr::get((new MetaData())->execute(), 'RequiresServer', false))
        {
            return;
        }

        $serverGroupId = Request::get('servergroup', false);

        if (!$serverGroupId)
        {
            throw new UserException($this->translate('productRequiresServer', [], ['packages.product.errors']));
        }

        $moduleName = ModuleConstants::getModuleName();

        if (ServersGroups::find($serverGroupId)->servers->where('type', $moduleName)->count() <= 0)
        {
            throw new UserException($this->translate('invalidServerType', ['moduleName' => $moduleName], ['packages.product.errors']));
        }
    }

    public function postLoadHtml(): void
    {
        parent::postLoadHtml();

        if (ProductConfigurationHelper::isRunAsProductAddon())
        {
            return;
        }

        $widget = new Widget();
        $widget->setTitle($this->translate('title', [], ['packages.product.productConfiguration.form']));

        $table = new TableSimple();

        $configurableOptions = is_callable(Config::get(ConfigSettings::CONFIG_OPTIONS_LOADER)) ? Config::get(ConfigSettings::CONFIG_OPTIONS_LOADER)(Request::get('id')) : Config::get(ConfigSettings::CONFIG_OPTIONS);

        foreach ($configurableOptions as $configOption)
        {
            if ($configOption instanceof ConfigurableOptionsGroup)
            {
                foreach ($configOption->getOptions() as $option)
                {
                    $table->addRecord(new Record([ConfigOptionFullNameFormatter::buildFullNameContainer($option->getFullName())]));
                }
                continue;
            }

            $table->addRecord(new Record([ConfigOptionFullNameFormatter::buildFullNameContainer($configOption->getFullName())]));
        }

        $widget->addElement($table);

        $button = new ButtonSuccess();
        $button->setTitle($this->translate('button_submit', [], ['packages.product.productConfiguration.form']));
        $button->onClick(new ModalLoad(new CreateConfigurableOptions()));

        $container = new ContainerContentCentered();
        $container->addElement($button);
        $widget->addElement($container);

        $this->addElement((new Row)->addElement($widget));
    }
}