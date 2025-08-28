<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\LayoutProps;

trait LayoutPropsTrait
{

    public function setLayoutProp(LayoutProps $prop):self
    {
        $this->appendCss($prop->value);

        return $this;
    }
}