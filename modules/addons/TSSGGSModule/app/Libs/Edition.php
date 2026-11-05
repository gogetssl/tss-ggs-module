<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Distribution edition (GoGetSSL Module / TheSSLStore Module) - see app/Config/edition.php.
 * Self-contained on purpose: it is also used by the server module before the addon framework is booted.
 */
class Edition
{
    const GGS = 'ggs';
    const TSS = 'tss';

    /**
     * Name used by 1.2.x releases - email templates created under it are renamed to the edition name
     */
    const LEGACY_NAME = 'The SSL Store & GoGetSSL Module';

    /** Names editions used before (email templates created under them are renamed too) */
    const OLD_NAMES = ['TheSSLStore Plugin'];

    protected static $editions = [
        self::GGS => [
            'name'        => 'GoGetSSL Module',
            'brand'       => 'GoGetSSL',
            'description' => 'GoGetSSL module for WHMCS: sell and manage SSL/TLS certificates, ACME and AutoInstall SSL subscriptions and other security products.',
        ],
        self::TSS => [
            'name'        => 'TheSSLStore Module',
            'brand'       => 'TheSSLStore',
            'description' => 'TheSSLStore module for WHMCS: sell and manage SSL/TLS certificates, ACME and AutoInstall SSL subscriptions and other security products.',
        ],
    ];

    protected static $edition = null;

    public static function get()
    {
        if(self::$edition === null)
        {
            $config        = @include __DIR__ . '/../Config/edition.php';
            $edition       = is_array($config) && isset($config['edition']) ? strtolower(trim((string)$config['edition'])) : '';
            self::$edition = isset(self::$editions[$edition]) ? $edition : self::GGS;
        }

        return self::$edition;
    }

    public static function name()
    {
        return self::$editions[self::get()]['name'];
    }

    /** Short brand for labels the client sees when both editions are installed */
    public static function brand()
    {
        return self::$editions[self::get()]['brand'];
    }

    public static function description()
    {
        return self::$editions[self::get()]['description'];
    }

    /**
     * Names of all editions plus the legacy name - used to find email templates created by another edition or by 1.2.x
     */
    public static function knownNames()
    {
        $names = array_merge([self::LEGACY_NAME], self::OLD_NAMES);
        foreach(self::$editions as $edition)
        {
            $names[] = $edition['name'];
        }

        return array_values(array_unique($names));
    }

    /**
     * Certificate vendor (API credentials) of the edition: each edition works with its own account only -
     * TheSSLStore Module with TheSSLStore, the GoGetSSL Module with GoGetSSL
     */
    public static function vendors()
    {
        return self::get() === self::TSS ? ['tss'] : ['ggs'];
    }

    /**
     * Full WHMCS email template name for a template key, e.g. "GoGetSSL Module - Reissue"
     */
    public static function templateName($key)
    {
        return self::name() . ' - ' . $key;
    }
}
