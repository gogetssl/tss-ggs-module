<?php

namespace ModulesGarden\TSSGGSModule\Components\Label;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Size;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Type;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\SizeTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TextTrait;

/**
 * Class Form
 */
class Label extends AbstractComponent
{
    use AjaxTrait;
    use TextTrait;
    use SizeTrait;
    use CssContainerTrait;
    use TitleTrait;

    public const COMPONENT = 'Label';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'text',
        ]);

        $this->setType(Type::DEFAULT);
        $this->setText($this->translate('text'));
        $this->setSize(Size::SMALL);
    }

    public function setType(string $type): self
    {
        $this->setSlot('type', $type);

        return $this;
    }


    public function displayAsStatusLabel(bool $labelStatus = true): self
    {
        $this->setSlot('displayAsStatusLabel', $labelStatus);

        return $this;
    }

    public function setBackgroundColor(string $color): self
    {
        $this->setSlot('backgroundColor', $color);

        return $this;
    }

    public function setTextColor(string $color): self
    {
        $this->setSlot('textColor', $color);

        return $this;
    }

    public function setTooltip(string $message): self
    {
        $this->setSlot('tooltip', $message);

        return $this;
    }
}
