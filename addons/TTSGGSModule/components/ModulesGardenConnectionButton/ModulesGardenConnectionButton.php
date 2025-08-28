<?php

namespace ModulesGarden\TTSGGSModule\Components\ModulesGardenConnectionButton;

use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ActionOnClickTrait;

class ModulesGardenConnectionButton extends Container
{
    use ActionOnClickTrait;
    use UrlTrait;
    use TextTrait;

    public const COMPONENT = 'ModulesGardenConnectionButton';
}
