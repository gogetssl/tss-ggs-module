<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Registrars
 *
 * @var id
 * @var registrar
 * @var setting
 * @var value
 */
class Registrars extends \WHMCS\Module\RegistrarSetting
{
    /*
     * Returns list of active whmcs registrar modules
     */
    public function getActiveList()
    {
        return $this->query()->getQuery()->select(['registrar'])->groupBy('registrar')->lists('registrar');
    }
}
