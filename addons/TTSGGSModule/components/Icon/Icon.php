<?php

namespace ModulesGarden\TTSGGSModule\Components\Icon;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;

/**
 * Class Form
 */
class Icon extends AbstractComponent
{
    use CssContainerTrait;
    use TitleTrait;

    public const COMPONENT = 'Icon';

    public function setIcon($icon):self
    {
        $this->appendCss('mdi mdi-' . $icon);

        return $this;
    }

    public function setContentCentered():self
    {
        $this->appendCss("lu-d-flex lu-justify-content-center");

        return $this;
    }
}
