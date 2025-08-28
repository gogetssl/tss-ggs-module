<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Forms;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Container\ContainerColumn;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Decorator\Decorator;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Packages\Product\Enums\ConfigSettings;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\AbstractConfigurableOption;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptionsGroups\ConfigurableOptionsGroup;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\Builders\ConfigurableOptionsBuilder;

class CreateConfigurableOptions extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = \ModulesGarden\TTSGGSModule\Packages\Product\UI\Providers\CreateConfigurableOptions::class;

    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::simple($this);
    }

    public function loadHtml(): void
    {
        //@todo move this to provider
        $configurableOptions = is_callable(Config::get(ConfigSettings::CONFIG_OPTIONS_LOADER)) ? Config::get(ConfigSettings::CONFIG_OPTIONS_LOADER)(Request::get('id')) : Config::get(ConfigSettings::CONFIG_OPTIONS);

        $row = new Row();

        foreach ($configurableOptions as $configOption)
        {
            $element = $configOption instanceof ConfigurableOptionsGroup ? $this->buildGroup($configOption) : $this->buildSinlge($configOption);

            $containerClass = Config::get(ConfigSettings::CONFIG_OPTIONS_CONTAINER, ContainerColumn::class);
            $container      = new $containerClass();

            $container->addElement($element);

            if ($configOption instanceof ConfigurableOptionsGroup)
            {
                (new Decorator($container))
                    ->childrenSize()
                    ->fitToParent();
            }

            $row->addElement($container);
        }

        $this->addElement($row);
    }

    protected function generateSwitcher(AbstractConfigurableOption $configOption): Switcher
    {
        $switcher = new Switcher();
        $switcher->setName($configOption->getName());
        $switcher->setTitle($configOption->getFullName());
        $switcher->setDefaultValue(true);

        return $switcher;
    }

    protected function buildSinlge(AbstractConfigurableOption $configOption)
    {
        $section = new Container();

        (new ConfigurableOptionsBuilder($this))->addFieldInContainer($section, $this->generateSwitcher($configOption));

        $section->addClass('lu-m-r-2x');
        $section->addClass('lu-m-l-2x');

        return $section;
    }

    protected function buildGroup(ConfigurableOptionsGroup $configOptionGroup)
    {
        $section = new Widget();
        $section->setTitle($this->translate($configOptionGroup->getName()));

        foreach ($configOptionGroup->getOptions() as $configOptionInGroup)
        {
            (new ConfigurableOptionsBuilder($this))->addFieldInContainer($section, $this->generateSwitcher($configOptionInGroup));
        }

        return $section;
    }

}