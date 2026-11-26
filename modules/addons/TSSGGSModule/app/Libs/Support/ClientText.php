<?php

namespace TSSGGSModule\App\Libs\Support;

/**
 * Client-area texts of shared code (errors and messages a client sees) from the client area language files
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
     * Plain text (not HTML-encoded) of a client area language key, null when it is not available (the server module
     * is not loaded in this request, or the key is missing)
     */
    public static function lookup(string ...$path)
    {
        try
        {
            return class_exists('\TSSGGSModule\Server\Text') ? \TSSGGSModule\Server\Text::plain(...$path) : null;
        }
        catch(\Throwable $e)
        {
            return null;
        }
    }
}
