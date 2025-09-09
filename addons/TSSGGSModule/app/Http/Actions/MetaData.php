<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Actions;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\AddonController;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

class MetaData extends AddonController
{
    public function execute($params = null)
    {
        return [
            'DisplayName'       => Config::get('configuration.systemName'),
            'APIVersion'        => '1.1',
            'RequiresServer'    => Config::get('configuration.requiresServer', true),
            'DefaultNonSSLPort' => '8006',
            'DefaultSSLPort'    => '8006',
            //            'ServiceSingleSignOnLabel'                => 'Login to myModule Client',
            //            'AdminSingleSignOnLabel'                  => 'Login to myModule Admin',
            //            'ListAccountsUniqueIdentifierDisplayName' => 'Domain',
            //            'ListAccountsUniqueIdentifierField'       => 'domain',
            //            'ListAccountsProductField'                => 'configoption1',
        ];
    }
}
