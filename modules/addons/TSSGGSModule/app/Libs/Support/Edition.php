<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Support;

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
            'releaseRepo' => 'gogetssl/whmcs-module',   // GitHub releases the module updates from (App\Libs\Update)
            'cdnDir'      => 'ggsmodule',               // https://ggssl.com/cdn/releases/<cdnDir>/ (fallback copy)
            'description' => 'GoGetSSL Module for WHMCS: sell and manage SSL/TLS certificates, ACME and AutoInstall SSL subscriptions and other security products.',
        ],
        self::TSS => [
            'name'        => 'TheSSLStore Plugin',
            'brand'       => 'TheSSLStore',
            'tablePrefix' => 'TSSPlugin',
            'releaseRepo' => 'thestores/whmcs',
            'cdnDir'      => 'tssplugin',
            'description' => 'TheSSLStore Plugin for WHMCS: sell and manage SSL/TLS certificates, ACME and AutoInstall SSL subscriptions and other security products.',
        ],
    ];

    protected static $edition = null;

    public static function get()
    {
        if(self::$edition === null)
        {
            $config        = @include __DIR__ . '/../../Config/edition.php';
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

    /** Brand of each vendor, for texts about a vendor's own orders and products */
    const BRANDS = [self::GGS => 'GoGetSSL', self::TSS => 'TheSSLStore'];

    /** The documentation (Wiki) of each edition - SAMPLE addresses until the Wikis are published */
    const WIKI = [self::GGS => 'https://github.com/gogetssl/whmcs-module/wiki', self::TSS => 'https://github.com/thesslstore/whmcs-plugin/wiki'];

    public static function wiki()
    {
        return self::WIKI[self::get()] ?? self::WIKI[self::TSS];
    }

    /** tbladdonmodules setting that holds the connected second vendor ('' / none) */
    const CONNECTED_SETTING = 'connected_vendor';

    /** @var string|null the connected vendor, read once per request */
    protected static $connected = null;

    /**
     * Certificate vendors (API credentials) the module works with: the edition's own vendor (TheSSLStore Plugin:
     * TheSSLStore, GoGetSSL Module: GoGetSSL), plus the other vendor when the administrator connected it
     * (Settings > API, "Connect ... products") - then only that vendor's exclusive products are offered (CrossVendor).
     */
    public static function vendors()
    {
        $vendors = [self::primaryVendor()];
        if(self::connectedVendor() !== '')
        {
            $vendors[] = self::connectedVendor();
        }

        return $vendors;
    }

    /** The edition's own vendor: 'tss' | 'ggs' */
    public static function primaryVendor()
    {
        return self::get() === self::TSS ? self::TSS : self::GGS;
    }

    /** The vendor that can be connected besides the edition's own one */
    public static function otherVendor()
    {
        return self::primaryVendor() === self::TSS ? self::GGS : self::TSS;
    }

    /** The connected second vendor, '' when none */
    public static function connectedVendor()
    {
        if(self::$connected === null)
        {
            self::$connected = '';
            try
            {
                $value = strtolower(trim((string)\WHMCS\Database\Capsule::table('tbladdonmodules')
                    ->where('module', 'configuration_TSSGGSModule')->where('setting', self::CONNECTED_SETTING)->value('value')));
                self::$connected = $value === self::otherVendor() ? $value : '';
            }
            catch(\Throwable $e)
            {
                // no database yet (early boot): the edition's own vendor only
            }
        }

        return self::$connected;
    }

    /** Connects the other vendor ('' disconnects it) */
    public static function setConnectedVendor($vendor)
    {
        $vendor = strtolower(trim((string)$vendor)) === self::otherVendor() ? self::otherVendor() : '';
        \WHMCS\Database\Capsule::table('tbladdonmodules')->updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => self::CONNECTED_SETTING],
            ['value' => $vendor]
        );
        self::$connected = $vendor;
    }

    /** Whether $vendor ('tss' / 'GGS' ...) is the connected second vendor */
    public static function isConnected($vendor)
    {
        return self::connectedVendor() !== '' && strtolower((string)$vendor) === self::connectedVendor();
    }

    /** 'GoGetSSL' / 'TheSSLStore' for a vendor key ('ggs', 'TSS' ...); the edition's brand for an unknown one */
    public static function brandOf($vendor)
    {
        return self::BRANDS[strtolower((string)$vendor)] ?? self::brand();
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
     * Installs made before 1.3.0 carry the folder name instead; App\Libs\Support\TablePrefix renames those.
     */
    /** A value of this edition's definition ('releaseRepo', 'cdnDir', 'brand' ...) */
    public static function value($key)
    {
        return self::$editions[self::get()][$key] ?? null;
    }

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
     * by App\Libs\Support\ModuleRename, and its tables by App\Libs\Support\TablePrefix.
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
        return basename(dirname(__DIR__, 3));
    }
}
