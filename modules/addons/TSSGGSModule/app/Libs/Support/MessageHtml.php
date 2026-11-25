<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Support;

/**
 * Client messages that allow HTML (processing guide, issued SSL message - General settings and per product).
 *
 * WHMCS HTML-encodes every posted field, so these texts were stored encoded and a client saw "<b>" as text. They are
 * now decoded when saved (posted), and a text stored encoded before is decoded when read (stored).
 */
class MessageHtml
{
    /** setting keys of these messages */
    const KEYS = ['processingGuide', 'issuedMessage'];

    /** a posted field as the administrator wrote it */
    public static function posted($text)
    {
        return html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * A stored text as HTML: one saved before the fix has no "<" at all but entities such as &lt; or &quot; - it is
     * decoded once. A text saved since keeps its own "<" and is returned as it is.
     */
    public static function stored($text)
    {
        $text = (string)$text;
        if(strpos($text, '<') === false && preg_match('/&(lt|gt|quot|#0?39|amp);/', $text))
        {
            return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        }

        return $text;
    }

    /** the message keys of a settings array, read as HTML */
    public static function read(array $settings)
    {
        foreach(self::KEYS as $key)
        {
            if(isset($settings[$key]) && is_string($settings[$key]))
            {
                $settings[$key] = self::stored($settings[$key]);
            }
        }

        return $settings;
    }
}
