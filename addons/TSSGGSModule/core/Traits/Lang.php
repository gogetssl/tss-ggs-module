<?php

namespace ModulesGarden\TSSGGSModule\Core\Traits;

use ModulesGarden\TSSGGSModule\Core\ServiceLocator;

/**
 * @deprecated
 */
trait Lang
{
    /**
     * @var null|\ModulesGarden\TSSGGSModule\Core\Lang\Lang
     */
    protected $lang = null;

    /**
     * @return void
     * @deprecated
     */
    public function loadLang()
    {
        if ($this->lang === null)
        {
            $this->lang = ServiceLocator::call('lang');
        }
    }
}
