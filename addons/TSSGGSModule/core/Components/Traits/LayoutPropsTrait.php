<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Traits;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\LayoutProps;

trait LayoutPropsTrait
{

    public function setLayoutProp(LayoutProps $prop):self
    {
        $this->appendCss($prop->value);

        return $this;
    }
}