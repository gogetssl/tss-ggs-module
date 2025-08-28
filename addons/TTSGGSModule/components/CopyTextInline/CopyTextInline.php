<?php

namespace ModulesGarden\TTSGGSModule\Components\CopyTextInline;

use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TranslatorTrait;

class CopyTextInline extends IconButton
{
    use TranslatorTrait;
    use TextTrait;

    public const COMPONENT = 'CopyTextInline';

    protected const TARGET_TYPE_CLIPBOARD          = 'clipboard';
    protected const TARGET_TYPE_COMPONENT          = 'component';
    protected const TARGET_TYPE_COMPONENT_ID       = 'componentId';
    protected const TARGET_TYPE_COMPONENT_NAME     = 'componentName';
    protected const TARGET_TYPE_COMPONENT_SELECTOR = 'componentSelector';
    protected const TARGET_TYPE_FOCUSED            = 'focused';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'text_copied',
            'title'
        ]);

        $this->setTargetType(self::TARGET_TYPE_CLIPBOARD);
    }

    public function setTargetComponent(AbstractComponent $targetComponent): self
    {
        $this->setTargetType(self::TARGET_TYPE_COMPONENT);
        $this->setTarget($targetComponent);

        return $this;
    }

    public function setTargetComponentId(string $id): self
    {
        $this->setTargetType(self::TARGET_TYPE_COMPONENT_ID);
        $this->setTarget($id);

        return $this;
    }

    public function setTargetComponentName(string $name): self
    {
        $this->setTargetType(self::TARGET_TYPE_COMPONENT_NAME);
        $this->setTarget($name);

        return $this;
    }

    public function setTargetComponentSelector(string $selector): self
    {
        $this->setTargetType(self::TARGET_TYPE_COMPONENT_SELECTOR);
        $this->setTarget($selector);

        return $this;
    }

    public function setTargetFocused()
    {
        $this->setTargetType(self::TARGET_TYPE_FOCUSED);

        return $this;
    }

    public function hideIcon()
    {
        $this->setSlot('hideIcon', true);

        return $this;
    }

    protected function setTargetType(string $type)
    {
        $this->setSlot('targetType', $type);
    }

    protected function setTarget($target)
    {
        $this->setSlot('target', $target);
    }
}
