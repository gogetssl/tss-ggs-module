<?php

namespace ModulesGarden\TSSGGSModule\Components\Badge;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\OutlineTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;

/**
 * Class Form
 */
class Badge extends AbstractComponent
{
    use AjaxTrait;
    use TextTrait;
    use TitleTrait;
    use OutlineTrait;

    public const COMPONENT = 'Badge';

    public function __construct()
    {
        parent::__construct();

        $this->setType(Color::DEFAULT);
    }

    public function setType(string $type)
    {
        $this->setSlot('type', $type);

        return $this;
    }
}
