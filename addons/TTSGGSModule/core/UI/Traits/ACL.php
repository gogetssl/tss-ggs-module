<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Traits;


use ModulesGarden\TTSGGSModule\Core\UI\Interfaces\ClientArea;

/**
 * @deprecated
 */
trait ACL
{
    protected $isAdminAcl;

    public function setIsAdminAcl($isAdmin)
    {
        $this->isAdminAcl = $isAdmin;

        return $this;
    }

    public function validateElement($element)
    {
        if ($this->isCoreElementAcl($element) || $this->isAdminAcl && $this->checkIsAdminArea($element) || !$this->isAdminAcl && $this->checkIsClientArea($element))
        {
            return true;
        }

        return false;
    }

    protected function isCoreElementAcl($element)
    {
        return strpos(get_class($element), 'ModulesGarden\TTSGGSModule\Core') !== false;
    }

    protected function checkIsAdminArea($element)
    {
        return $element instanceof AdminArea;
    }

    protected function checkIsClientArea($element)
    {
        return $element instanceof ClientArea;
    }
}
