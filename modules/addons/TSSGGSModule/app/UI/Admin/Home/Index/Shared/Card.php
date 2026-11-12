<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

/**
 * Dashboard panel: a header (icon tile, title, aside), then HTML blocks and, where needed, framework components
 * (e.g. a button that opens a modal), and an optional footer link pinned to the bottom.
 * Styles in resources/assets/css/dashboard.css and theme.css, scoped to .tssggs-dash.
 */
abstract class Card extends Container
{
    /**
     * Panel root class and header. Called from the panel's own loadHtml(): AJAX-loaded components are re-created on
     * the server, so CSS classes set from outside would be lost.
     *
     * @param string $tone tile colour: '' | ok | bad | warn | info | dark
     */
    protected function start(string $icon, string $title, string $aside = '', string $tone = '', string $css = ''): void
    {
        $this->setCss(trim('tssggs-dcard ' . $css));
        $this->addElement(self::html('<div class="tssggs-dcard__head"><h3>' . self::tile($icon, $tone) . self::e($title) . '</h3>' . $aside . '</div>'));
    }

    protected function body(string $html): void
    {
        $this->addElement(self::html('<div class="tssggs-dcard__body">' . $html . '</div>'));
    }

    /** Footer link at the bottom of the panel */
    protected function foot(string $href, string $icon, string $label, bool $arrow = true): void
    {
        $this->addElement(self::html('<a class="tssggs-dcard__foot" href="' . self::e($href) . '">' . self::icon($icon) . '<span>' . self::e($label) . '</span>'
            . ($arrow ? self::icon('arrow-right') : '') . '</a>'));
    }

    public static function html(string $html): Text
    {
        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);

        return $text;
    }

    /** Inline SVG icon (fixed size set by the CSS of the place it is in) */
    public static function icon(string $name): string
    {
        return Icons::svg($name);
    }

    /** Square icon tile (panel headers, list items) */
    public static function tile(string $icon, string $tone = ''): string
    {
        return '<span class="tssggs-tile' . ($tone !== '' ? ' is-' . $tone : '') . '">' . self::icon($icon) . '</span>';
    }

    public static function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /** Outline button link with a trailing arrow */
    public static function link(string $href, string $label, string $class = ''): string
    {
        return '<a class="tssggs-dbtn' . ($class !== '' ? ' ' . $class : '') . '" href="' . self::e($href) . '">' . self::e($label) . self::icon('arrow-right') . '</a>';
    }

    /** Small outline button (inside rows) */
    public static function button(string $href, string $label, string $icon = '', bool $external = false): string
    {
        return '<a class="tssggs-sbtn" href="' . self::e($href) . '"' . ($external ? ' target="_blank" rel="noopener noreferrer"' : '') . '>'
            . ($icon !== '' ? self::icon($icon) : '') . self::e($label) . '</a>';
    }

    /** Status pill: ok | warn | bad | muted, with an icon (default: a dot) */
    public static function status(string $tone, string $label, string $icon = '', string $title = ''): string
    {
        return '<span class="tssggs-pill is-' . $tone . '"' . ($title !== '' ? ' title="' . self::e($title) . '"' : '') . '>'
            . ($icon !== '' ? self::icon($icon) : '<span class="tssggs-pill__dot"></span>') . self::e($label) . '</span>';
    }

    /** List row: icon, name with an optional second line, value on the right (HTML) */
    public static function row(string $icon, string $name, string $sub, string $valueHtml): string
    {
        return '<div class="tssggs-row">' . self::icon($icon) . '<span class="tssggs-row__name">' . self::e($name)
            . ($sub !== '' ? '<small>' . self::e($sub) . '</small>' : '') . '</span><span class="tssggs-row__val">' . $valueHtml . '</span></div>';
    }
}
