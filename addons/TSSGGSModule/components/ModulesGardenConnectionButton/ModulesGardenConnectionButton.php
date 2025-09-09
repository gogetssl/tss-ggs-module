<?php

namespace ModulesGarden\TSSGGSModule\Components\ModulesGardenConnectionButton;

use ModulesGarden\TSSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionOnClickTrait;

class ModulesGardenConnectionButton extends Container
{
    use ActionOnClickTrait;
    use UrlTrait;
    use TextTrait;

    public const COMPONENT = 'ModulesGardenConnectionButton';
}
