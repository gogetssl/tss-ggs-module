<?php

namespace TSSGGSModule\App\Kernel;

use TSSGGSModule\App\Libs\Support\Icons;

/**
 * Smarty templates of the module (the Smarty that WHMCS ships).
 *
 * A template is looked up in overrides/templates first, then in resources/templates - a reseller copies a file to
 * the same path under overrides/templates and changes it there; a module update never touches that folder.
 *
 * Every {$variable} is HTML-escaped. Print ready HTML with {$variable nofilter}.
 *
 *   {t key='x' html=true}             a text with its own HTML, not escaped
 *   {t key='filter'}                  text of the page's own group (Page::LANG), ":name" replaced by name=...
 *   {t key='admin.menu.home'}         a full key works too
 *   {icon name='check'}               inline SVG icon
 *   {url action='index' p=2}          link to an action of the current page
 *   {url page='settings'}             link to another page
 *   {tile icon='check' tone='ok'}     square icon tile
 *   {pill tone='ok' label=$text}      status pill
 *   {tip key='x_description'}         the "?" hint beside a label
 */
class View
{
    public static function render(string $template, array $data, Page $page): string
    {
        $smarty = new \Smarty();
        $smarty->setTemplateDir([Kernel::path('overrides', 'templates'), Kernel::path('resources', 'templates')]);
        $smarty->setCompileDir(self::compileDir());
        $smarty->setEscapeHtml(true);
        $smarty->setErrorReporting(E_ALL & ~E_NOTICE & ~E_WARNING);

        $smarty->registerPlugin('function', 't', function(array $params) use ($page) {
            $key = (string)($params['key'] ?? '');
            // html=true: a text of the module that holds its own HTML (<b>), printed as it is
            $html = !empty($params['html']);
            unset($params['key'], $params['html']);

            return $html ? $page->t($key, $params) : htmlspecialchars($page->t($key, $params), ENT_QUOTES, 'UTF-8');
        });
        $smarty->registerPlugin('function', 'icon', function(array $params) {
            return Icons::svg((string)($params['name'] ?? ''), (string)($params['class'] ?? ''));
        });
        $smarty->registerPlugin('function', 'url', function(array $params) use ($page) {
            $target = (string)($params['page'] ?? '');
            $action = (string)($params['action'] ?? 'index');
            unset($params['page'], $params['action']);

            return htmlspecialchars($target !== '' ? Page::pageUrl($target, $action, $params) : $page->url($action, $params), ENT_QUOTES, 'UTF-8');
        });

        // {tile icon='check' tone='ok'}: square icon tile (panel headers, list items); tone '' | ok | bad | warn | info | dark
        $smarty->registerPlugin('function', 'tile', function(array $params) {
            $tone = preg_replace('/[^a-z]/', '', (string)($params['tone'] ?? ''));

            return '<span class="tssggs-tile' . ($tone !== '' ? ' is-' . $tone : '') . '">' . Icons::svg((string)($params['icon'] ?? '')) . '</span>';
        });
        // {pill tone='ok' label=$text icon='check' title=$hint}: status pill; tone ok | warn | bad | muted; without an icon: a dot
        $smarty->registerPlugin('function', 'pill', function(array $params) {
            $e     = function($value) {
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            };
            $icon  = (string)($params['icon'] ?? '');
            $title = (string)($params['title'] ?? '');

            return '<span class="tssggs-pill is-' . preg_replace('/[^a-z]/', '', (string)($params['tone'] ?? 'muted')) . '"' . ($title !== '' ? ' title="' . $e($title) . '"' : '') . '>'
                . ($icon !== '' ? Icons::svg($icon) : '<span class="tssggs-pill__dot"></span>') . $e($params['label'] ?? '') . '</span>';
        });

        // {tip key='...description'} or {tip text=$hint}: the "?" beside a label, with the text as its hint; nothing
        // when the key has no text
        $smarty->registerPlugin('function', 'tip', function(array $params) use ($page) {
            $key  = (string)($params['key'] ?? '');
            $text = (string)($params['text'] ?? '');
            if($text === '' && $key !== '')
            {
                $own  = $page::LANG . '.' . $key;
                $text = $page->lang()->has($own) ? $page->lang()->get($own) : ($page->lang()->has($key) ? $page->lang()->get($key) : '');
            }
            if($text === '')
            {
                return '';
            }
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

            return '<i class="mod-ico-16 mod-field-help mod-tip tssggs-icobox tssggs-tip" tabindex="0" data-title="' . $text . '" aria-label="' . $text . '">'
                . Icons::svg('help-circle-outline') . '</i>';
        });

        // in every template; a page's own data of the same name wins
        $smarty->assign('moduleName', Kernel::module());
        $smarty->assign('pageName', $page->name());
        // {$assets}/assets/js/..., {$assets}/assets/img/...; {$version} for cache busting
        $smarty->assign('assets', Kernel::assetsUrl());
        $smarty->assign('version', Layout::version());
        // for <input type="hidden" name="token"> of a form that also works without htmx
        $smarty->assign('token', function_exists('generate_token') ? (string)\generate_token('plain') : '');
        $smarty->assign($data);

        try
        {
            return $smarty->fetch($template . '.tpl');
        }
        catch(\Throwable $e)
        {
            // several parts of a page asked for at once (lazy blocks) can compile the same new template at the same
            // moment, right after an update: the second look finds it compiled
            usleep(150000);

            return $smarty->fetch($template . '.tpl');
        }
    }

    protected static function compileDir(): string
    {
        $dir = (string)($GLOBALS['templates_compiledir'] ?? '');
        if($dir !== '' && is_dir($dir) && is_writable($dir))
        {
            return $dir;
        }
        $dir = Kernel::path('storage', 'templates_c');
        if(!is_dir($dir))
        {
            @mkdir($dir, 0755, true);
        }

        return is_writable($dir) ? $dir : sys_get_temp_dir();
    }
}
