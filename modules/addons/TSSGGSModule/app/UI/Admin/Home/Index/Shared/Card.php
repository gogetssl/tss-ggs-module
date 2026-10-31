<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

/**
 * Dashboard card (design: github.com/gogetssl/ssl-plugin-refresh, dashboard route): a header with icon and title,
 * then HTML blocks and, where needed, framework components (e.g. a button that opens a modal).
 * Styles in resources/assets/css/dashboard.css, scoped to .tssggs-dash.
 */
abstract class Card extends Container
{
    /**
     * Card root class and header. Called from the card's own loadHtml(): AJAX-loaded components are re-created on
     * the server, so CSS classes set from outside would be lost.
     */
    protected function start(string $icon, string $title, string $aside = ''): void
    {
        $this->setCss('tssggs-dcard');
        $this->addElement(self::html('<div class="tssggs-dcard__head"><h3>' . self::icon($icon) . self::e($title) . '</h3>' . $aside . '</div>'));
    }

    protected function body(string $html): void
    {
        $this->addElement(self::html('<div class="tssggs-dcard__body">' . $html . '</div>'));
    }

    public static function html(string $html): Text
    {
        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);

        return $text;
    }

    public static function icon(string $name): string
    {
        return '<i class="mdi mdi-' . $name . '" aria-hidden="true"></i>';
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

    /** Status pill: ok | warn | bad | muted */
    public static function status(string $tone, string $label): string
    {
        return '<span class="tssggs-pill is-' . $tone . '"><span class="tssggs-pill__dot"></span>' . self::e($label) . '</span>';
    }
}
