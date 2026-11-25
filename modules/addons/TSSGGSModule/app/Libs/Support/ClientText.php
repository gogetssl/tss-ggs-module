<?php

namespace TSSGGSModule\App\Libs\Support;

/**
 * Client-area texts of shared code (errors and messages a client sees) from the server module language files
 * ($_LANG['clientText'][...] in modules/servers/TSSGGSModule/langs, incl. langs/overrides), English when the
 * server module language is not loaded (cron, API calls) or the key is missing.
 */
class ClientText
{
    /**
     * @param string $key     key under $_LANG['clientText']
     * @param string $english text used when the language files have no translation
     * @param array  $replace :name: placeholders => values
     */
    public static function get(string $key, string $english, array $replace = []): string
    {
        $text = self::lookup('clientText', $key);
        if($text === null)
        {
            $text = $english;
        }
        foreach($replace as $name => $value)
        {
            $text = str_replace(':' . $name . ':', (string)$value, $text);
        }

        return $text;
    }

    /**
     * Plain text (not HTML-encoded) of a server module language key, null when it is not available
     */
    public static function lookup(string ...$path)
    {
        $lang = '\TSSGGSModule\Server\core\Lang';
        try
        {
            // only a language the server module kernel has loaded: calling Lang before it would start it without
            // its language folder and leave it empty for the rest of the request
            if(!class_exists($lang, false))
            {
                return null;
            }
            $instance = new \ReflectionProperty($lang, 'instance');
            $instance->setAccessible(true);
            if($instance->getValue() === null)
            {
                return null;
            }
            $text = html_entity_decode((string)$lang::absoluteT(...$path), ENT_QUOTES, 'UTF-8');
        }
        catch(\Throwable $e)
        {
            return null;
        }
        if($text === '' || $text === end($path) || strpos($text, '_LANG[') !== false)
        {
            return null;
        }

        return $text;
    }
}
