<?php

namespace ModulesGarden\TTSGGSModule\Components\Badge;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\OutlineTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;

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
