<?php

namespace ModulesGarden\TTSGGSModule\Components\FormLabel;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;

/**
 * Class Form
 */
class FormLabel extends AbstractComponent
{
    use ComponentsContainerTrait;
    use CssContainerTrait;
    use TextTrait;

    public const COMPONENT = 'FormLabel';
}
