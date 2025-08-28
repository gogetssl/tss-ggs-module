<?php

namespace ModulesGarden\TTSGGSModule\Components\CopyPasswordInline;

use ModulesGarden\TTSGGSModule\Components\CopyTextInline\CopyTextInline;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ActionOnClickTrait;

class CopyPasswordInline extends CopyTextInline
{
    use ActionOnClickTrait;

    public const COMPONENT = 'CopyPasswordInline';

    public function setVisible()
    {
        $this->setSlot('is_visible', true);

        return $this;
    }
}
