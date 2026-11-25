<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;

/**
 * The module's admin layout around a page: header, main menu, page title, the page's content and the assets
 * (template admin/layout.tpl). The menu is read from app/Config/menu/admin.yml, the same file the old framework
 * uses, so both show the same menu while pages move over.
 */
class Layout
{
    /** tabs always visible in the bar; the other sections are in the "More" dropdown */
    const PRIMARY = ['home', 'products', 'pricing', 'certificateLog', 'emails', 'settings'];

    public static function render(Page $page, string $body): string
    {
        return View::render('admin/layout', [
            'body'      => $body,
            'bodyClass' => $page::BODY_CLASS,
            'heading'   => $page::HEADING,
            // the setup wizard has no module header and no menu
            'chrome'    => $page::CHROME,
            'title'     => $page->title(),
            'module'    => Edition::name(),
            'edition'   => Edition::get(),
            'homeUrl'   => Page::pageUrl('home'),
            'menu'      => self::menu($page),
            'token'     => function_exists('generate_token') ? (string)\generate_token('plain') : '',
        ], $page);
    }

    /** @return array[] name, title, url, icon, active, items (the same shape, for a dropdown) */
    public static function menu(Page $page): array
    {
        $menu = [];
        $more = ['name' => 'more', 'title' => $page->lang()->get('admin.menu.more'), 'url' => '#', 'icon' => 'dots-horizontal', 'active' => false, 'items' => []];
        foreach(self::menuConfig() as $name => $icon)
        {
            $item = ['name' => $name, 'title' => $page->lang()->get('admin.menu.' . $name), 'url' => Page::pageUrl($name), 'icon' => $icon,
                     'active' => strtolower($name) === strtolower($page->name()), 'items' => []];
            if(in_array($name, self::PRIMARY, true))
            {
                $menu[] = $item;
                continue;
            }
            // the dropdown shows the open section's name instead of "More"
            if($item['active'])
            {
                $more['active'] = true;
                $more['title']  = $item['title'];
            }
            $more['items'][] = $item;
        }
        if($more['items'])
        {
            $menu[] = $more;
        }

        return $menu;
    }

    /** @return array page name => icon, in the order of app/Config/menu/admin.yml */
    protected static function menuConfig(): array
    {
        $items = [];
        $name  = null;
        foreach(@file(Kernel::path('app', 'Config', 'menu', 'admin.yml')) ?: [] as $line)
        {
            if(preg_match('/^([A-Za-z][A-Za-z0-9]*):/', $line, $m))
            {
                $name         = $m[1];
                $items[$name] = '';
            }
            elseif($name !== null && preg_match('/^\s+icon:\s*[\'"]?([^\'"\r\n]+)/', $line, $m))
            {
                $items[$name] = trim($m[1]);
            }
        }

        return $items;
    }

    public static function version(): string
    {
        $yml = (string)@file_get_contents(Kernel::path('app', 'Config', 'configuration.yml'));

        return preg_match('/^version:\s*[\'"]?([0-9A-Za-z.\-]+)/m', $yml, $m) ? $m[1] : '0';
    }

    /** @return string content of a page that only says something (tone: '' | ok | warn | bad) */
    public static function message(Page $page, string $text, string $tone = ''): string
    {
        return \ModulesGarden\TSSGGSModule\App\Libs\Support\Html::noticeText($text, $tone);
    }

    public static function notFound(Page $page): string
    {
        return self::message($page, $page->lang()->get('admin.kernel.notFound'), 'warn');
    }

    public static function error(Page $page, \Throwable $e): string
    {
        return self::message($page, $page->lang()->get('admin.kernel.error', ['message' => $e->getMessage()]), 'bad');
    }
}
