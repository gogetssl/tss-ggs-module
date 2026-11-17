<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Admin-area texts of the server module (service tab, module buttons): a translation file of their own,
 * separate from the client-area language files:
 *
 *   modules/servers/TSSGGSModule/langs/admin/<language>.php            $_LANG['key'] = 'text';
 *   modules/servers/TSSGGSModule/langs/admin/overrides/<language>.php  own changes, kept on module updates
 *
 * The language is the one of the signed-in administrator (WHMCS My Account), else the system default; English
 * fills in what a language does not have.
 */
class AdminText
{
    private static $texts;

    /**
     * @param string $key     key in langs/admin/<language>.php
     * @param string $english text used when no language file has the key
     * @param array  $replace :name: placeholders => values
     */
    public static function get(string $key, string $english, array $replace = []): string
    {
        if(self::$texts === null)
        {
            self::$texts = self::load(self::language());
        }
        $text = isset(self::$texts[$key]) && is_string(self::$texts[$key]) && self::$texts[$key] !== '' ? self::$texts[$key] : $english;
        foreach($replace as $name => $value)
        {
            $text = str_replace(':' . $name . ':', (string)$value, $text);
        }

        return $text;
    }

    /** get() as HTML (the service tab and button labels are HTML) */
    public static function html(string $key, string $english, array $replace = []): string
    {
        return htmlspecialchars(self::get($key, $english, $replace), ENT_QUOTES, 'UTF-8');
    }

    /** Language of the signed-in administrator, else the WHMCS default language */
    public static function language(): string
    {
        $language = '';
        try
        {
            if(!empty($_SESSION['adminid']))
            {
                $language = (string)\WHMCS\Database\Capsule::table('tbladmins')->where('id', (int)$_SESSION['adminid'])->value('language');
            }
            if($language === '')
            {
                $language = (string)\WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'Language')->value('value');
            }
        }
        catch(\Throwable $e)
        {
        }
        $language = strtolower(trim($language));

        return preg_match('/^[a-z_-]+$/', $language) ? $language : 'english';
    }

    /** english, then the language, then the overrides of both */
    private static function load(string $language): array
    {
        $dir   = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'TSSGGSModule' . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR . 'admin';
        $texts = [];
        foreach(array_unique(['english', $language]) as $name)
        {
            foreach([$dir . DIRECTORY_SEPARATOR . $name . '.php', $dir . DIRECTORY_SEPARATOR . 'overrides' . DIRECTORY_SEPARATOR . $name . '.php'] as $file)
            {
                if(is_file($file))
                {
                    $texts = array_replace($texts, self::read($file));
                }
            }
        }

        return $texts;
    }

    private static function read(string $file): array
    {
        $_LANG = [];
        include $file;

        return is_array($_LANG) ? $_LANG : [];
    }

    /** tests: forget the loaded texts */
    public static function reset()
    {
        self::$texts = null;
    }
}
