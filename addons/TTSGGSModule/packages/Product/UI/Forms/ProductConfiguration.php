<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Forms;

use ModulesGarden\TTSGGSModule\App\Http\Actions\MetaData;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Container\ContainerContentCentered;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Exceptions\UserException;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ServersGroups;
use ModulesGarden\TTSGGSModule\Packages\Product\Enums\ConfigSettings;
use ModulesGarden\TTSGGSModule\Packages\Product\Helpers\ProductConfiguration as ProductConfigurationHelper;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptionsGroups\ConfigurableOptionsGroup;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\Formatters\ConfigOptionFullNameFormatter;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\Modals\CreateConfigurableOptions;

class ProductConfiguration extends \ModulesGarden\TTSGGSModule\Components\Form\AbstractForm implements AdminAreaInterface
{
    use TranslatorTrait;

    protected string $provider = \ModulesGarden\TTSGGSModule\Packages\Product\UI\Providers\ProductConfiguration::class;

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