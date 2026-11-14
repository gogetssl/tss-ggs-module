<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Distribution edition (GoGetSSL Module / TheSSLStore Plugin) - see app/Config/edition.php.
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
    const OLD_NAMES = ['TheSSLStore Module'];

    protected static $editions = [
        self::GGS => [
            'name'        => 'GoGetSSL Module',
            'brand'       => 'GoGetSSL',
            'tablePrefix' => 'GGSModule',
            'description' => 'GoGetSSL module for WHMCS: sell and manage SSL/TLS certificates, ACME and AutoInstall SSL subscriptions and other security products.',
        ],
        self::TSS => [
            'name'        => 'TheSSLStore Plugin',
            'brand'       => 'TheSSLStore',
            'tablePrefix' => 'TSSPlugin',
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
     * TheSSLStore Plugin with TheSSLStore, the GoGetSSL Module with GoGetSSL
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

    /**
     * Name every database table of this edition starts with, without the underscore.
     * It follows the edition, not the folder the module is installed in, so the two editions never share a table.
     * Installs made before 1.3.0 carry the folder name instead; App\Libs\TablePrefix renames those.
     */
    public static function tablePrefix()
    {
        return self::$editions[self::get()]['tablePrefix'];
    }

    /**
     * Full name of a table of this edition, e.g. "TSSPlugin_Requests"
     */
    public static function table($name)
    {
        return self::tablePrefix() . '_' . $name;
    }

    /**
     * Folders this edition was installed in before 1.4.0. WHMCS keeps the module's own name in its records -
     * the product's server type, the addon's settings - so an install upgraded from one of those is moved over
     * by App\Libs\ModuleRename, and its tables by App\Libs\TablePrefix.
     *
     * Only this edition's former names belong here: where both editions are installed, neither may adopt
     * the other's products.
     */
    public static function legacyFolders()
    {
        return self::get() === self::TSS ? ['tssmodule'] : ['ggsmodule'];
    }

    /**
     * Folder the module is installed in, which is the name WHMCS knows it by
     */
    public static function folder()
    {
        return basename(dirname(__DIR__, 2));
    }
}
