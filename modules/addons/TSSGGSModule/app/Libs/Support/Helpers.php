<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Support;

use ModulesGarden\TSSGGSModule\Components\Link\Link;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TSSGGSModule\Core\WHMCS\URL\Admin;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ConfigurableOptions;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;

class Helpers
{
    use Concerns\Currencies;
    use Concerns\FormOptions;
    use Concerns\ProductResync;

    public static function isWildcardEnabled($whmcsProductId)
    {
        $whmcsProduct = Product::find($whmcsProductId);

        if(!$whmcsProduct)
        {
            return false;
        }

        $configurableOptionsService = new ConfigurableOptions($whmcsProduct);
        $configurableOptionModel = $configurableOptionsService->getConfigurableOptionByName('sans_wildcard');

        if($configurableOptionModel)
        {
            return !(bool)$configurableOptionModel->hidden;
        }
        else
        {
            return false;
        }
    }

    public static function validateDomain($domain) {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain)
            && preg_match("/^.{1,253}$/", $domain)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)
            && preg_match('/[^.\d]/', $domain));
    }

    public static function getConfiguredApis()
    {
        // the vendors that have credentials (TheSSLStore: of the mode that is switched on)
        $apis = [];
        foreach(['GGS', 'TSS'] as $vendor)
        {
            if(SSLTrustCenterApi::credentialsOf($vendor) !== null)
            {
                $apis[$vendor] = SSLTrustCenterApi::forVendor($vendor);
            }
        }

        return $apis;
    }

    public static function getApiByServiceId($whmcsServiceId)
    {
        $service = Service::find($whmcsServiceId);
        if(!$service)
        {
            return false;
        }
        $vendor = $service->product->configoption1;
        $apis    = Helpers::getConfiguredApis();

        if(isset($apis[$vendor]))
        {
            return $apis[$vendor];
        }
        return false;
    }

    public static function monthsToBillingPeriod($months)
    {
        $dictionary = [
            '1'  => 'monthly',
            '3'  => 'quarterly',
            '6'  => 'semiannually',
            '12' => 'annually',
            '24' => 'biennially',
            '36' => 'triennially',
        ];

        return $dictionary[$months] ?: false;
    }

    public static function billingPeriodToMonths($billingPeriod)
    {
        $dictionary = [
            'Monthly'       => '1',
            'Quarterly'     => '3',
            'Semi-Annually' => '6',
            'Annually'      => '12',
            'Biennially'    => '24',
            'Triennially'   => '36',
        ];

        return $dictionary[$billingPeriod] ?: false;
    }

    public static function getAdminAreaClientLink($clientId)
    {
        $client = Client::find($clientId);

        if(!$client)
        {
            return false;
        }

        $link  = new Link;
        $title = $client->companyname ?: $client->firstname . ' ' . $client->lastname;
        $link->setTitle($title);
        $link->setUrl(Admin::clientSummary($clientId));

        return $link;
    }

    public static function vendorToDisplay($vendor)
    {
        // vendor keys are stored lower case ("ggs", "tss"); older callers pass them upper case
        return strtolower((string)$vendor) === 'ggs' ? 'GoGetSSL' : 'TheSSLSTORE';
    }

    public static function getChecksumData()
    {
        $rootDirs = [
            dirname(__DIR__, 3),
            dirname(__DIR__, 5) . '/servers/TSSGGSModule'
        ];

        $excludedDirs = [
            'modules/addons/TSSGGSModule/langs',
            'modules/addons/TSSGGSModule/storage',
            'modules/servers/TSSGGSModule/langs',
            'modules/servers/TSSGGSModule/storage'
        ];

        $checksumData = [];

        foreach($rootDirs as $rootDir)
        {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir));

            foreach($iterator as $file)
            {
                if($file->isFile())
                {
                    $relativePath = str_replace(dirname($rootDir, 3) . '/', '', $file->getPathname());
                    foreach($excludedDirs as $excludedDir)
                    {
                        if(strpos($relativePath, $excludedDir) === 0)
                        {
                            continue 2;
                        }
                    }

                    $checksumData[$relativePath] = md5_file($file->getPathname());
                }
            }
        }

        return $checksumData;
    }

    public static function compareChecksumData($checksumData = false, $previousChecksumData = false)
    {
        $checksumFile = dirname(__DIR__, 3) . '/storage/app/checksum.json';

        if(!$checksumData)
        {
            $checksumData = Helpers::getChecksumData();
        }

        if(!$previousChecksumData)
        {
            $previousChecksumData = file_exists($checksumFile) ? json_decode(file_get_contents($checksumFile), true) : [];
        }

        $compare = [
            'unchanged' => [],
            'new'       => [],
            'modified'  => [],
            'deleted'   => [],
        ];


        if(!$previousChecksumData)
        {
            Helpers::updateChecksumData($checksumData);
        }
        else
        {
            foreach($checksumData as $file => $checksum)
            {
                if(!isset($previousChecksumData[$file]))
                {
                    $compare['new'][] = $file;
                }
                elseif($previousChecksumData[$file] === $checksum)
                {
                    $compare['unchanged'][] = $file;
                }
                else
                {
                    $compare['modified'][] = $file;
                }
            }

            foreach($previousChecksumData as $file => $checksum)
            {
                if(!isset($checksumData[$file]))
                {
                    $compare['deleted'][] = $file;
                }
            }
        }

        return $compare;
    }

    public static function updateChecksumData($checksumData = false)
    {
        $checksumFile = dirname(__DIR__, 3) . '/storage/app/checksum.json';

        if(!$checksumData)
        {
            $checksumData = Helpers::getChecksumData();
        }

        file_put_contents($checksumFile, json_encode($checksumData, JSON_PRETTY_PRINT));
    }
}
