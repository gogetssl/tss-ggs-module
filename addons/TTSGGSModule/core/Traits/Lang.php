<?php

namespace ModulesGarden\TTSGGSModule\Core\Traits;

use ModulesGarden\TTSGGSModule\Core\ServiceLocator;

/**
 * @deprecated
 */
trait Lang
{
    /**
     * @var null|\ModulesGarden\TTSGGSModule\Core\Lang\Lang
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
