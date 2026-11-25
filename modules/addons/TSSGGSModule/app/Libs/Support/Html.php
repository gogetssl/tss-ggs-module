<?php

namespace TSSGGSModule\App\Libs\Support;

/**
 * The small pieces of HTML the module's pages build by hand, in one place: escaping, notices, hint boxes.
 * Styles: theme.css (#mod-root .tssggs-note, .tssggs-renewal-check). Use these instead of writing the markup again.
 *
 *   Html::e($text)                         escaped text
 *   Html::notice($html, 'ok')              notice box; tone '' (info) | ok | warn | bad; $html must be escaped already
 *   Html::noticeText($text, 'bad')         the same for plain text
 *   Html::hint($html, 'amber')             small hint under a field; tone amber | red | green
 */
class Html
{
    const NOTICE_TONES = ['', 'ok', 'warn', 'bad'];
    const HINT_TONES   = ['amber', 'red', 'green'];

    public static function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /** @param string $html escaped HTML */
    public static function notice(string $html, string $tone = ''): string
    {
        $tone = in_array($tone, self::NOTICE_TONES, true) ? $tone : '';

        return '<div class="tssggs-note' . ($tone !== '' ? ' is-' . $tone : '') . '">' . $html . '</div>';
    }

    public static function noticeText(string $text, string $tone = ''): string
    {
        return self::notice(self::e($text), $tone);
    }

    /** @param string $html escaped HTML */
    public static function hint(string $html, string $tone = 'amber'): string
    {
        $tone = in_array($tone, self::HINT_TONES, true) ? $tone : 'amber';

        return '<div class="tssggs-renewal-check"><p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--' . $tone . '">' . $html . '</p></div>';
    }
}
