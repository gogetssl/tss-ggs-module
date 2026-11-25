<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Certificates;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Products\ProductSettings;

/**
 * The message shown to the client once an SSL certificate is issued (Settings > SSL Certificates > Client Messages).
 *
 * Which text, first match wins (forClient):
 *  1. the product's own message (Products > Configuration)
 *  2. the text of the certificate family of the product (Sectigo SSL / DigiCert SSL), when its "Show this message" is
 *     on, in the client's language: the administrator's text of that language, else the built-in translation (server
 *     langs 'issuedMessageDefault', incl. langs/overrides), else the administrator's English text, else built-in English
 *  3. the admin's General message
 *
 * The settings form shows the built-in text in each box; a box left as it is stores nothing, so the install follows
 * the module's wording. S/MIME, code signing and document signing have no family: they keep 1 and 3 only.
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
    const NOT_SSL = ['smime', 'cs', 'ds', 'code-guard', 'mc', 'pci', 'lei'];

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

    // ---- per brand texts, per language (Settings > SSL Certificates > Client Messages) ---- //

    /** stored switch "Show this message" of a family; not stored yet: on, unless the install already has a General message */
    const SWITCH_PREFIX = 'issuedMessageOn_';

    /** built-in text of each language, null = the module ships no translation */
    private static $defaults = [];

    /** settings key of the administrator's text of a family in one language */
    public static function textKey($family, $language)
    {
        return 'issuedMessage_' . $family . '_' . self::language($language);
    }

    /** whether a general settings key is one of the per family / language texts (stored next to GeneralSettings::DEFAULTS) */
    public static function isTextKey($key)
    {
        return (bool)preg_match('/^issuedMessage_(' . implode('|', array_keys(self::FAMILIES)) . ')_[a-z0-9_-]+$/', (string)$key);
    }

    public static function enabled($family)
    {
        $stored = GeneralSettings::get(self::SWITCH_PREFIX . $family);
        if($stored === null || $stored === '')
        {
            // an install that already shows its own General message keeps it until the administrator turns this on
            return trim((string)GeneralSettings::get('issuedMessage')) === '';
        }

        return GeneralSettings::enabled(self::SWITCH_PREFIX . $family);
    }

    /** WHMCS languages, the default one first */
    public static function languages()
    {
        $languages = [];
        foreach((array)glob(ROOTDIR . '/lang/*.php') as $file)
        {
            $name = strtolower(basename($file, '.php'));
            if($name !== 'index' && preg_match('/^[a-z0-9_-]+$/', $name))
            {
                $languages[$name] = $name;
            }
        }
        $languages += ['english' => 'english'];
        ksort($languages);
        $default = self::defaultLanguage();
        if(isset($languages[$default]))
        {
            $languages = [$default => $default] + $languages;
        }

        return array_values($languages);
    }

    public static function defaultLanguage()
    {
        try
        {
            $language = (string)\WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'Language')->value('value');
        }
        catch(\Throwable $e)
        {
            $language = '';
        }

        return self::language($language !== '' ? $language : 'english');
    }

    /** whether the module ships the built-in text in this language (else the English one is used) */
    public static function translated($language)
    {
        return self::defaultText($language) !== null;
    }

    /**
     * Built-in text (HTML) of a family in a language - English when the module has no translation - with the
     * installation guide of the authority as a link
     */
    public static function builtin($family, $language)
    {
        $text = self::defaultText($language);
        if($text === null)
        {
            $text = (string)self::defaultText('english');
        }
        $guide = self::guide($family);

        return str_replace(
            [':brand:', ':link_start:', ':link_end:'],
            [
                htmlspecialchars(self::authorityName($family)),
                $guide !== '' ? '<a href="' . htmlspecialchars($guide) . '" target="_blank" rel="noopener noreferrer">' : '',
                $guide !== '' ? '</a>' : '',
            ],
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
        );
    }

    /** the administrator's text of a family in a language, '' when not changed from the built-in one */
    public static function stored($family, $language)
    {
        return trim((string)GeneralSettings::get(self::textKey($family, $language)));
    }

    /**
     * Text of a family for a client language: the administrator's text in that language, the built-in translation,
     * the administrator's English text, the built-in English one
     */
    public static function familyText($family, $language)
    {
        $text = self::stored($family, $language);
        if($text !== '')
        {
            return $text;
        }
        if(self::translated($language))
        {
            return self::builtin($family, $language);
        }
        $text = self::stored($family, 'english');

        return $text !== '' ? $text : self::builtin($family, 'english');
    }

    /** what the form posted for a family / language: '' (= follow the built-in text) when it is the built-in text */
    public static function toStore($family, $language, $posted)
    {
        $posted = trim((string)$posted);

        return self::same($posted, self::builtin($family, $language)) ? '' : $posted;
    }

    /**
     * The message a client sees once the certificate is issued: the product's own message, the text of the certificate
     * family (when its "Show this message" is on), the General message; '' when none
     */
    public static function forClient($packageId, $language)
    {
        $text = trim((string)ProductSettings::get($packageId)['issuedMessage']);
        if($text !== '')
        {
            return $text;
        }
        $family = self::family($packageId);
        if($family && self::enabled($family))
        {
            return self::familyText($family, $language);
        }

        return trim((string)GeneralSettings::get('issuedMessage'));
    }

    private static function same($a, $b)
    {
        $normalize = function($text) {
            return preg_replace('/\s+/u', ' ', trim(html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8')));
        };

        return $normalize($a) === $normalize($b);
    }

    private static function language($language)
    {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$language));
    }

    /** 'issuedMessageDefault' of the server module language file (and its langs/overrides file), plain text */
    private static function defaultText($language)
    {
        $language = self::language($language);
        if(!array_key_exists($language, self::$defaults))
        {
            $dir  = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'TSSGGSModule' . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR;
            $text = null;
            foreach([$dir . $language . '.php', $dir . 'overrides' . DIRECTORY_SEPARATOR . $language . '.php'] as $file)
            {
                if($language !== '' && is_file($file))
                {
                    $value = (function($file) {
                        $_LANG = [];
                        include $file;

                        return $_LANG['issuedMessageDefault'] ?? null;
                    })($file);
                    if(is_string($value) && trim($value) !== '')
                    {
                        $text = $value;
                    }
                }
            }
            self::$defaults[$language] = $text;
        }

        return self::$defaults[$language];
    }
}
