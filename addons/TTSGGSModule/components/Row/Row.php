<?php

namespace ModulesGarden\TTSGGSModule\Components\Row;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class Row extends AbstractComponent implements ComponentContainerInterface
{
    use AjaxTrait;
    use ComponentsContainerTrait;
    use CssContainerTrait;

    public const COMPONENT = 'Row';
}
