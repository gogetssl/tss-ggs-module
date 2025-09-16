<?php

namespace ModulesGarden\TSSGGSModule\App\Configuration\Addon\Activate;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;

/**
 * Runs before module activation actions
 */
class Before extends \ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Activate\Before
{
    /**
     * @param array $params
     * @return array
     */
    public function execute(array $params = [])
    {
        $return = parent::execute($params);

        EmailTemplates::createTemplates();

        return $return;
    }
}
