<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * Texts of the module: langs/english.php, then the language of the logged in admin (or the client), then the
 * reseller's own file in overrides/langs. Keys are flat ("admin.certificate_log.filter"); ":name" in a text is
 * replaced by the value given.
 */
class Lang
{
    protected static $cache = [];
    protected $texts;

    public function __construct(string $area = 'admin', string $language = null)
    {
        $language = strtolower(preg_replace('/[^A-Za-z0-9_\-]/', '', (string)($language ?: self::current($area))) ?: 'english');
        if(!isset(self::$cache[$language]))
        {
            $texts = [];
            foreach([Kernel::path('langs', 'english.php'), Kernel::path('langs', $language . '.php'), Kernel::path('overrides', 'langs', $language . '.php')] as $file)
            {
                $texts = self::read($file) + $texts;
            }
            self::$cache[$language] = $texts;
        }
        $this->texts = self::$cache[$language];
    }

    public function get(string $key, array $replace = []): string
    {
        $text = $this->texts[$key] ?? $key;
        if(!$replace)
        {
            return $text;
        }

        // whole names only: ":page" must not eat the start of ":pages"
        return preg_replace_callback('/:([A-Za-z_][A-Za-z0-9_]*)/', function($m) use ($replace) {
            return array_key_exists($m[1], $replace) ? (string)$replace[$m[1]] : $m[0];
        }, $text);
    }

    public function has(string $key): bool
    {
        return isset($this->texts[$key]);
    }

    protected static function current(string $area): string
    {
        try
        {
            if(class_exists('\\WHMCS\\Authentication\\CurrentUser'))
            {
                $user = new \WHMCS\Authentication\CurrentUser();
                if($area === 'admin' && $user->admin())
                {
                    return (string)$user->admin()->language;
                }
                if($area !== 'admin')
                {
                    return (string)($_SESSION['Language'] ?? ($user->client() ? $user->client()->language : ''));
                }
            }
        }
        catch(\Throwable $e)
        {
            // no session (cron, tests): English
        }

        return '';
    }

    /** @return array key => text; nested arrays of a file become dotted keys */
    protected static function read(string $file): array
    {
        if(!is_file($file))
        {
            return [];
        }
        $_LANG = [];
        include $file;

        $flat = [];
        $walk = function($items, $prefix) use (&$flat, &$walk) {
            foreach($items as $key => $value)
            {
                if(is_array($value))
                {
                    $walk($value, $prefix . $key . '.');
                }
                else
                {
                    $flat[$prefix . $key] = (string)$value;
                }
            }
        };
        $walk($_LANG, '');

        return $flat;
    }
}
