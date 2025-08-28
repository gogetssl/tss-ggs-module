<?php

namespace ModulesGarden\TTSGGSModule\Components\Alert;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\OutlineTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;

class Alert extends AbstractComponent
{
    use AjaxTrait;
    use TitleTrait;
    use TextTrait;
    use OutlineTrait;

    public const COMPONENT = 'Alert';


    /**
     * @param string $size
     * @return $this
     */
    public function setSize(string $size): self
    {
        $this->setSlot('size', $size);

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setType(string $title): self
    {
        $this->setSlot('type', $title);

        return $this;
    }

    /**
     * @param bool $showDismissButton
     * @return $this
     */
    public function showDismissButton(bool $showDismissButton = true): self
    {
        $this->setSlot('dismiss_button', $showDismissButton);

        return $this;
    }
}
