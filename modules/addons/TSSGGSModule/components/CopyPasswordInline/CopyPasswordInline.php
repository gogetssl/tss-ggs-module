<?php

namespace ModulesGarden\TSSGGSModule\Components\CopyPasswordInline;

use ModulesGarden\TSSGGSModule\Components\CopyTextInline\CopyTextInline;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionOnClickTrait;

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
