<?php

namespace ModulesGarden\TTSGGSModule\Components\Container;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\BorderTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

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
