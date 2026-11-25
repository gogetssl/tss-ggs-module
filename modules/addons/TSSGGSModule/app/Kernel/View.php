<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Icons;

/**
 * Smarty templates of the module (the Smarty that WHMCS ships).
 *
 * A template is looked up in overrides/templates first, then in resources/templates - a reseller copies a file to
 * the same path under overrides/templates and changes it there; a module update never touches that folder.
 *
 * Every {$variable} is HTML-escaped. Print ready HTML with {$variable nofilter}.
 *
 *   {t key='filter'}                  text of the page's own group (Page::LANG), ":name" replaced by name=...
 *   {t key='admin.menu.home'}         a full key works too
 *   {icon name='check'}               inline SVG icon
 *   {url action='index' p=2}          link to an action of the current page
 *   {url page='settings'}             link to another page
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
            unset($params['key']);

            return htmlspecialchars($page->t($key, $params), ENT_QUOTES, 'UTF-8');
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

        $smarty->assign($data);
        $smarty->assign('page', $page);
        $smarty->assign('moduleName', Kernel::module());
        // for <input type="hidden" name="token"> of a form that also works without htmx
        $smarty->assign('token', function_exists('generate_token') ? (string)generate_token('plain') : '');

        return $smarty->fetch($template . '.tpl');
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
