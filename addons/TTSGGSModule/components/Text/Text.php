<?php

namespace ModulesGarden\TTSGGSModule\Components\Text;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;

class Text extends AbstractComponent
{
    use TextTrait;
    use CssContainerTrait;

    public const COMPONENT = 'Text';
}
