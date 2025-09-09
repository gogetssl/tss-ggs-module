<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\FallbackTraits;

use ModulesGarden\TSSGGSModule\Core\UI\Traits\ACL;

trait BackwardCompatibilityTrait
{
    use ACL;

    public function getTemplateName()
    {
        return 'string:test';
    }

    /**
     * @param $mainContainer
     * @return void
     * @deprecated  - do not use!
     */


    public function getHtml()
    {
        return $this;
    }
}
