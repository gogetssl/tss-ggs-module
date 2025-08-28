<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\FallbackTraits;

/**
 * Trait ElementsTrait
 */
trait QueryDataProvider
{
    /**
     * @param $query
     * @return void
     * @deprecated - use setQuery instead
     */
    public function setData($query)
    {
        trigger_error('Function deprecated');
        $this->setQuery($query);
    }
}
