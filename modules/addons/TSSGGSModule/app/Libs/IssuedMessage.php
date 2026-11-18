<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;

/**
 * The message shown to the client once an SSL certificate is issued (Settings > SSL Certificates > Client Messages).
 *
 * Which text, first match wins:
 *  1. the product's own message (Products > Configuration)
 *  2. the admin's message for the certificate family of the product (Sectigo SSL / DigiCert SSL)
 *  3. the admin's General message
 *  4. the built-in text of the family, in the client's language, with the installation guide of the authority
 *     (server langs 'issuedMessageDefault', overridable per language in langs/overrides/<language>.php)
 *
 * S/MIME, code signing and document signing have no family: they keep 1 and 3 only.
 */
class IssuedMessage
{
    const FAMILIES = [
        'sectigo'  => [
            'name'   => 'Sectigo',
            'brands' => ['sectigo', 'comodo', 'positivessl', 'instantssl', 'essentialssl', 'enterprisessl'],
            // GoGetSSL resellers get the GoGetSSL wiki guide, TheSSLStore resellers the one of Sectigo
            'guide'  => [
                Edition::GGS => 'https://www.gogetssl.com/wiki/installation/digicert-sectigo-ssl-installation/',
                Edition::TSS => 'https://www.sectigo.com/knowledge-base/product/Certificate_Installation?KnowledgeBase%5Bpage%5D=1&KnowledgeBase%5Bsort%5D=relevance&KnowledgeBase%5BrefinementList%5D%5Bcategories%5D%5B0%5D=Certificate_Installation&KnowledgeBase%5BrefinementList%5D%5BcategoryGroup%5D%5B0%5D=Support_Categories',
            ],
        ],
        'digicert' => [
            'name'   => 'DigiCert',
            'brands' => ['digicert', 'geotrust', 'rapidssl', 'thawte', 'symantec'],
            'guide'  => [
                Edition::GGS => 'https://knowledge.digicert.com/general-information/ssl-certificate-installation-instructions-and-tutorials',
                Edition::TSS => 'https://knowledge.digicert.com/general-information/ssl-certificate-installation-instructions-and-tutorials',
            ],
        ],
    ];

    /** catalogue categories that are not SSL certificates */
    const NOT_SSL = ['smime', 'cs', 'ds', 'code-guard', 'mc', 'pci'];

    /**
     * 'sectigo' | 'digicert' | null. GoGetSSL-brand DV certificates are issued by Sectigo, OV / EV ones by DigiCert.
     */
    public static function family($packageId)
    {
        try
        {
            $product = (new ProductRepository())->getProductConfiguration((int)$packageId);
        }
        catch(\Throwable $e)
        {
            return null;
        }
        $remote = RemoteProduct::where('remoteId', $product['product_id'] ?? '')->where('vendor', $product['provider'] ?? '')
            ->first(['brand', 'validation', 'category']);
        if($remote && in_array(strtolower((string)$remote->category), self::NOT_SSL, true))
        {
            return null;
        }
        $brand = strtolower(trim((string)($product['brand'] ?? '') . ' ' . ($remote ? (string)$remote->brand : '')));

        foreach(self::FAMILIES as $key => $family)
        {
            foreach($family['brands'] as $needle)
            {
                if(strpos($brand, $needle) !== false)
                {
                    return $key;
                }
            }
        }
        if(strpos($brand, 'gogetssl') !== false)
        {
            return strtoupper((string)($remote ? $remote->validation : '')) === 'DV' ? 'sectigo' : 'digicert';
        }

        return null;
    }

    /**
     * The text the administrator set for the product (steps 1-3), '' when none: the caller then uses the built-in one
     */
    public static function configured($packageId, $family)
    {
        $text = trim((string)ProductSettings::get($packageId)['issuedMessage']);
        if($text === '' && $family)
        {
            $text = trim((string)GeneralSettings::get('issuedMessage_' . $family));
        }

        return $text !== '' ? $text : trim((string)GeneralSettings::get('issuedMessage'));
    }

    /** installation guide of the family for this edition */
    public static function guide($family)
    {
        return self::FAMILIES[$family]['guide'][Edition::get()] ?? '';
    }

    /** display name of the family's authority */
    public static function authorityName($family)
    {
        return self::FAMILIES[$family]['name'] ?? '';
    }
}
