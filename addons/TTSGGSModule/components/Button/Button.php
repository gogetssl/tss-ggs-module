<?php

namespace ModulesGarden\TTSGGSModule\Components\Button;

use ModulesGarden\TTSGGSModule\Core\Components\FallbackTraits\ButtonFallbackTrait;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ActionOnClickTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\LayoutPropsTrait;

class Button extends FormField
{
    use ButtonFallbackTrait;
    use ActionOnClickTrait;
    use LayoutPropsTrait;

    public const COMPONENT   = 'Button';
    public const TYPE_BUTTON = 'button';
    public const TYPE_CANCEL = 'cancel';
    public const TYPE_SUBMIT = 'submit';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'title',
        ]);

        $this->setTitle($this->translate('title'));
        $this->setType(self::TYPE_BUTTON);
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->setSlot('icon', 'lu-mdi mdi mdi-' . $icon);

        return $this;
    }

    /**
     * @param string $size
     * @return Button
     */
    public function setSize(string $size): self
    {
        $this->setSlot('size', $size);

        return $this;
    }

    /**
     * @param string $type
     * @return Button
     */
    public function setType(string $type): self
    {
        $this->setSlot('type', $type);

        return $this;
    }

    /**
     * @param string $title
     * @return self
     **/
    public function setTitle(string $title): self
    {
        $this->setSlot('title', $title);

        return $this;
    }
}
