<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\UI\GroupBuilders;

use ModulesGarden\TSSGGSModule\Components\Form\Builder\GroupBuilders\DefaultBuilder;
use ModulesGarden\TSSGGSModule\Packages\Product\UI\FieldFactories\ConfigOptionSwitcherFactory;

class ConfigOptionSwitcherBuilder extends DefaultBuilder
{
    protected function findBuilder($name): string
    {
        return ConfigOptionSwitcherFactory::class;
    }
}