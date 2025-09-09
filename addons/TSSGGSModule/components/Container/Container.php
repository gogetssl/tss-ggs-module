<?php

namespace ModulesGarden\TSSGGSModule\Components\Container;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\BorderTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

/**
 * Class Form
 */
class Container extends AbstractComponent implements ComponentContainerInterface
{
    use AjaxTrait;
    use ComponentsContainerTrait;
    use CssContainerTrait;

    public const COMPONENT = 'Container';

    public function setContent($content)
    {
        $this->setSlot('content', $content);
    }
}
