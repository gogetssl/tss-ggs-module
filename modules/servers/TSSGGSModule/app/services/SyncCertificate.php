<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\TSSGGSModule\App\Libs\CertificateSyncHelpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * Admin "Sync Certificate" button - the SSLCertificates cron flow for a single service,
 * without checking the /services/updates request.
 */
class SyncCertificate
{
    public function run($params)
    {
        $certificate = CertificateSyncHelpers::getCertificatesQuery()
            ->where('tblhosting.id', $params['serviceid'])
            ->first();

        if(!$certificate)
        {
            throw new \Exception('The certificate can be synchronized only for an Active service with the "Configuration Submitted" SSL order status');
        }

        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

        CertificateSyncHelpers::syncCertificate($certificate->toArray(), $addonConfig);

        return 'success';
    }
}
