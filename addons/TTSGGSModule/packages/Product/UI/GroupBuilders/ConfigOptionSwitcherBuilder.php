<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\GroupBuilders;

use ModulesGarden\TTSGGSModule\Components\Form\Builder\GroupBuilders\DefaultBuilder;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\FieldFactories\ConfigOptionSwitcherFactory;

class ConfigOptionSwitcherBuilder extends DefaultBuilder
{
    protected function findBuilder($name): string
    {
        return ConfigOptionSwitcherFactory::class;
    }
}