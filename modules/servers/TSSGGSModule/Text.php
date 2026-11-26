<?php

namespace TSSGGSModule\Server;

use WHMCS\Database\Capsule;

/**
 * Client area texts of the module: langs/english.php, then the client's language, then the reseller's own file in
 * langs/overrides (same format, only the keys that change). The files hold $_LANG as nested groups; a text is found
 * by its path, e.g. Text::get('sslTools', 'title').
 *
 * get() answers HTML-safe text (it goes into pages as it is); plain() answers the raw text or null.
 * A missing text answers its last key, so a page never breaks over a missing translation.
 */
class Text
{
    /** the texts of the request's language, loaded on first use */
    protected static $texts;
    protected static $language;

    /** the group a page template's t() starts in: client pages use $_LANG['serverCA']['home'] */
    protected $base;

    public function __construct(array $base = [])
    {
        $this->base = $base;
    }

    /** {$text->t('key')} / {$text->t('group', 'key')} in a template: the page's own group first */
    public function t(...$path): string
    {
        $texts = self::texts();
        foreach($this->base as $name)
        {
            if(isset($texts[$name]) && is_array($texts[$name]))
            {
                $texts = $texts[$name];
            }
        }

        return self::find($texts, $path);
    }

    /** A text from the top of the language file, HTML-safe */
    public static function get(...$path): string
    {
        return self::find(self::texts(), $path);
    }

    /** The raw text (not HTML-encoded), null when the language files do not have it */
    public static function plain(...$path)
    {
        $texts = self::texts();
        foreach($path as $key)
        {
            if(!is_array($texts) || !isset($texts[$key]))
            {
                return null;
            }
            $texts = $texts[$key];
        }

        return is_string($texts) && $texts !== '' ? $texts : null;
    }

    /** The language the texts are in: the client's (session, account), else the system's, else English */
    public static function language(): string
    {
        if(self::$language === null)
        {
            $language = '';
            try
            {
                if(!empty($_SESSION['Language']))
                {
                    $language = (string)$_SESSION['Language'];
                }
                elseif(!empty($_SESSION['uid']))
                {
                    $language = (string)Capsule::table('tblclients')->where('id', (int)$_SESSION['uid'])->value('language');
                }
                if($language === '')
                {
                    $language = (string)Capsule::table('tblconfiguration')->where('setting', 'Language')->value('value');
                }
            }
            catch(\Throwable $e)
            {
                // no database (tests): English
            }
            self::$language = strtolower(preg_replace('/[^A-Za-z0-9_\-]/', '', $language)) ?: 'english';
        }

        return self::$language;
    }

    protected static function find($texts, array $path): string
    {
        $last = '';
        foreach($path as $key)
        {
            $last = (string)$key;
            if(!is_array($texts) || !isset($texts[$key]))
            {
                return htmlentities($last);
            }
            $texts = $texts[$key];
            if(is_string($texts))
            {
                return htmlentities($texts);
            }
        }

        return htmlentities($last);
    }

    protected static function texts(): array
    {
        if(self::$texts === null)
        {
            $dir   = __DIR__ . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR;
            $texts = [];
            foreach(array_unique(['english', self::language()]) as $language)
            {
                foreach([$dir . $language . '.php', $dir . 'overrides' . DIRECTORY_SEPARATOR . $language . '.php'] as $file)
                {
                    $texts = array_replace_recursive($texts, self::read($file));
                }
            }
            self::$texts = $texts;
        }

        return self::$texts;
    }

    protected static function read(string $file): array
    {
        if(!is_file($file))
        {
            return [];
        }
        $_LANG = [];
        include $file;

        return is_array($_LANG) ? $_LANG : [];
    }
}
