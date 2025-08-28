<?php

namespace ModulesGarden\TTSGGSModule\App\Libs;

use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Components\Link\Link;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\PaymentGateway;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductGroup;
use ModulesGarden\TTSGGSModule\Core\WHMCS\URL\Admin;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\Quantity;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\SubOption\SubOption;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ConfigurableOptions;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ConfigurableOptionsGroup;
use WHMCS\Database\Capsule;

class Helpers
{
    static public function debugLog($action, $request = [], $response = [])
    {
        logmodulecall("TTSGGSModule Debug Log", $action, $request, $response, $response);
    }

    public static function getConfiguredApis()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();
        $apis                = [];

        if($moduleConfiguration['credentials']['ggs']['PartnerCode'])
        {
            $apis['GGS'] = new SSLTrustCenterApi('GGS', $moduleConfiguration['credentials']['ggs']['PartnerCode'], $moduleConfiguration['credentials']['ggs']['AuthToken']);
        }

        if($moduleConfiguration['credentials']['tss']['PartnerCode'] || $moduleConfiguration['credentials']['tss']['TestPartnerCode'])
        {
            if($moduleConfiguration['credentials']['tss']['OperationMode'] == 'sandbox')
            {
                $apis['TSS'] = new SSLTrustCenterApi('TSS', $moduleConfiguration['credentials']['tss']['TestPartnerCode'], $moduleConfiguration['credentials']['tss']['TestAuthToken']);
            }
            else
            {
                $apis['TSS'] = new SSLTrustCenterApi('TSS', $moduleConfiguration['credentials']['tss']['PartnerCode'], $moduleConfiguration['credentials']['tss']['AuthToken']);
            }
        }

        return $apis;
    }


    public static function getConfigurableOptionsData($productId, $currencyId)
    {
        return Capsule::table('tblproductconfiglinks')
                      ->join('tblproductconfigoptions', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
                      ->join('tblproductconfigoptionssub', 'tblproductconfigoptions.id', '=', 'tblproductconfigoptionssub.configid')
                      ->leftJoin('tblpricing', 'tblproductconfigoptionssub.id', '=', 'tblpricing.relid')
                      ->where('tblproductconfiglinks.pid', $productId)
                      ->where('tblpricing.type', 'configoptions')
                      ->where('tblpricing.currency', $currencyId)
                      ->where('tblproductconfigoptions.optiontype', '4')
                      ->select(['tblproductconfigoptions.optionname', 'tblproductconfigoptionssub.id as subId', 'tblpricing.annually', 'tblpricing.biennially', 'tblpricing.triennially'])
                      ->get();
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


    public static function getSelectedCurrency()
    {
        $moduleConfiguration = (new AddonModuleRepository())->getModuleConfiguration();
        $selectedCurrencyId  = (int)$moduleConfiguration['financeSettings']['currency'];

        if($selectedCurrencyId)
        {
            $selectedCurrency = Currency::findOrFail($selectedCurrencyId);
        }
        else
        {
            $selectedCurrency = Currency::where('default', "1")->first();
        }

        return $selectedCurrency;
    }

    public static function clientCurrencyToSelectedCurrency($amount, $clientId)
    {
        $amount           = floatval($amount);
        $selectedCurrency = Helpers::getSelectedCurrency();
        $clientCurrency   = Client::findOrFail($clientId)->currency;

        return \convertCurrency($amount, $clientCurrency->id, $selectedCurrency->id);
    }

    public static function apiCurrencyToSelectedCurrency($amount)
    {
        $amount              = floatval($amount);
        $moduleConfiguration = (new AddonModuleRepository())->getModuleConfiguration();
        $currencyRate        = $moduleConfiguration['financeSettings']['rate'] ?: 1;

        return $amount * $currencyRate;
    }

    public static function formatSelectedCurrency($amount)
    {
        $selectedCurrency = Helpers::getSelectedCurrency();

        return (string)\formatCurrency($amount, $selectedCurrency->id);
    }

    public static function getCurrencySymbol($currencyId = false)
    {
        if($currencyId)
        {
            $currency = Currency::findOrFail($currencyId);
        }
        else
        {
            $currency = Helpers::getSelectedCurrency();
        }

        return $currency->prefix ?: $currency->code;
    }

    public static function getTaxedValue($value, $taxRate, $taxRate2)
    {
        global $CONFIG;

        if($CONFIG["TaxType"] == "Exclusive")
        {
            if($CONFIG["TaxL2Compound"])
            {
                $value = $value + $value * $taxRate / 100;
                $value = $value + $value * $taxRate2 / 100;
            }
            else
            {
                $value = $value + $value * $taxRate / 100 + $value * $taxRate2 / 100;
            }
        }

        return $value;
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
        return ($vendor == 'GGS') ? 'GoGetSSL' : 'TheSSLSTORE';
    }

    public static function getCurrencyOptions()
    {
        $currencies      = Currency::get();
        $currencyOptions = [];

        foreach($currencies as $currency)
        {
            $currencyOptions[$currency->id] = $currency->code;
        }

        return $currencyOptions;
    }

    public static function getProductOptions()
    {
        $products       = Product::where('servertype', 'TTSGGSModule')->get();
        $productOptions = [
            0 => 'all',
        ];


        foreach($products as $product)
        {
            $productOptions[$product->id] = $product->name;
        }

        return $productOptions;
    }

    public static function getBrandOptions()
    {
        $products     = Product::where('servertype', 'TTSGGSModule')->get();
        $brandOptions = [];

        foreach($products as $product)
        {
            $brandOptions[$product->configoption3] = $product->configoption3;
        }

        return array_unique($brandOptions);
    }

    public static function getProductGroupOptions()
    {
        $productGroups       = ProductGroup::get();
        $productGroupOptions = [];

        foreach($productGroups as $productGroup)
        {
            $productGroupOptions[$productGroup->id] = $productGroup->name;
        }

        return $productGroupOptions;
    }

    public static function getDaysOptions()
    {
        return [3, 7, 14, 21, 30];
    }

    public static function get30DaysOptions()
    {
        $options = [];

        for($i = 0; $i <= 30; $i++)
        {
            $options[$i] = $i;
        }
        return $options;
    }

    public static function getClientOptions()
    {
        $options = [];

        $clients = Client::get();
        foreach($clients as $client)
        {
            $options[$client->id] = $client->firstname . ' ' . $client->lastname;
        }

        return $options;
    }

    public static function getPayMethodOptions()
    {
        $options = [];

        $gateways = PaymentGateway::where('setting', 'name')->get();

        foreach($gateways as $gateway)
        {
            $options[$gateway->gateway] = $gateway->value;
        }

        return $options;
    }


    public static function getChecksumData()
    {
        $rootDirs = [
            dirname(__DIR__, 2),
            dirname(__DIR__, 4) . '/servers/TTSGGSModule'
        ];

        $excludedDirs = [
            'modules/addons/TTSGGSModule/langs',
            'modules/addons/TTSGGSModule/storage',
            'modules/servers/TTSGGSModule/langs',
            'modules/servers/TTSGGSModule/storage'
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
        $checksumFile = dirname(__DIR__, 2) . '/storage/app/checksum.json';

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
        $checksumFile = dirname(__DIR__, 2) . '/storage/app/checksum.json';

        if(!$checksumData)
        {
            $checksumData = Helpers::getChecksumData();
        }

        file_put_contents($checksumFile, json_encode($checksumData, JSON_PRETTY_PRINT));
    }

    public static function reSyncProducts()
    {
        RemoteProduct::synchronize();

        $remoteProducts = RemoteProduct::get();

        foreach($remoteProducts as $remoteProduct)
        {
            $whmcsProduct = $remoteProduct->getWhmcsProduct();

            if($whmcsProduct)
            {
                $remoteProductData = $remoteProduct->rawData;

                $includedSan         = (int)$remoteProductData['san']['included']['single'];
                $includedSanWildcard = (int)$remoteProductData['san']['included']['wildcard'];

                $productRepository = new ProductRepository();
                $productRepository->updateProductConfiguration($whmcsProduct->id, [
                    'category'              => $remoteProduct->category,
                    'included_san'          => $includedSan,
                    'included_san_wildcard' => $includedSanWildcard,
                ]);


                $optionNames = [];

                if($remoteProductData['san']['single_allowed'])
                {
                    $optionNames['single'] = [
                        'name'         => 'sans',
                        'nameFriendly' => 'Additional Single domain SAN\'s',
                        'unit'         => 'SAN'
                    ];
                }

                if($remoteProductData['san']['wildcard_allowed'])
                {
                    $optionNames['wildcard'] = [
                        'name'         => 'sans_wildcard',
                        'nameFriendly' => 'Additional Wildcard domain SAN\'s',
                        'unit'         => 'SAN'
                    ];
                }

                foreach($optionNames as $remoteOptionName => $localOptionNames)
                {
                    $localOptionName         = $localOptionNames['name'];
                    $localOptionNameFriendly = $localOptionNames['nameFriendly'];
                    $localOptionUnitName     = $localOptionNames['unit'];
                    $min                     = $remoteProductData['san']['min'] ?: 0;
                    $max                     = $remoteProductData['san']['max'] ?: 0;
                    $included                = 0;

                    if($remoteOptionName == 'single')
                    {
                        $included = $includedSan;
                    }
                    elseif($remoteOptionName == 'wildcard')
                    {
                        $included = $includedSanWildcard;
                    }

                    $max = $max - ($included - $min);
                    $min = $min - $included;

                    if($min <= 0)
                    {
                        $min = 0;
                    }

                    if($max <= 0)
                    {
                        $max = 0;
                    }

                    $configurableOptionsService = new ConfigurableOptions($whmcsProduct);
                    $configurableOptionModel    = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

                    if($configurableOptionModel)
                    {
                        $configurableOptionModel->qtyminimum = $min;
                        $configurableOptionModel->qtymaximum = $max;
                        $configurableOptionModel->save();
                    }
                    else
                    {
                        $addonConfig  = (new AddonModuleRepository())->getModuleConfiguration();
                        $currencyId   = (int)$addonConfig['financeSettings']['currency'];
                        $currencyRate = $addonConfig['financeSettings']['rate'] ?: 1;
                        $profitMargin = floatval($addonConfig['financeSettings']['profitMargin']);


                        $configurableOptionsGroupService = new ConfigurableOptionsGroup();
                        $group                           = $configurableOptionsGroupService->getFirstOrCreateRelated($whmcsProduct);

                        $configurableOption = (new Quantity($localOptionName, $localOptionNameFriendly))->setRange($min, $max);
                        $subOption          = (new SubOption($localOptionUnitName));

                        $configurableOption->addOption($subOption);
                        $configurableOption->setGroupId($group->id);
                        $configurableOption->create($whmcsProduct);

                        $configurableOptionsService = new ConfigurableOptions($whmcsProduct);
                        $configurableOptionModel    = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

                        if(!$configurableOptionModel)
                        {
                            throw new \Exception("Unable to update configurable option");
                        }

                        $subOptionModel = $configurableOptionModel->suboptions()->first();
                        $insertData     = [];

                        foreach($remoteProductData['prices'] as $remotePriceData)
                        {
                            $billingPeriod = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                            if($billingPeriod === false)
                            {
                                continue;
                            }

                            if(isset($remotePriceData['san'][$remoteOptionName]['selling']))
                            {
                                $price = floatval($remotePriceData['san'][$remoteOptionName]['selling']);
                            }
                            else
                            {
                                continue;
                            }

                            $price                      = $price + ($price * $profitMargin / 100);
                            $currencyPrice              = $price * $currencyRate;
                            $insertData[$billingPeriod] = $currencyPrice;
                        }

                        Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOptionModel->id)->update($insertData);
                    }
                }
            }
        }
    }

    public static function getCountryOptions()
    {
        return [
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "IO" => "British Indian Ocean Territory",
            "BN" => "Brunei Darussalam",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos (Keeling) Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo",
            "CD" => "Congo, the Democratic Republic of the",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "CI" => "Cote D'Ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands (Malvinas)",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and Mcdonald Islands",
            "VA" => "Holy See (Vatican City State)",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran, Islamic Republic of",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Lao People's Democratic Republic",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libyan Arab Jamahiriya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macao",
            "MK" => "Macedonia, the Former Yugoslav Republic of",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "MX" => "Mexico",
            "FM" => "Micronesia, Federated States of",
            "MD" => "Moldova, Republic of",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territory, Occupied",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RE" => "Reunion",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syrian Arab Republic",
            "TW" => "Taiwan, Province of China",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania, United Republic of",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "UM" => "United States Minor Outlying Islands",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VE" => "Venezuela",
            "VN" => "Viet Nam",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.s.",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe"
        ];
    }
}