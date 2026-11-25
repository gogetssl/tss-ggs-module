<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * Base of a page. A page class holds the actions (public methods) of one mg-page; each returns a Response:
 *
 *   return $this->view('admin/certificateLog/index', $data);   full page; htmx gets the same template's fragment
 *   return $this->fragment('admin/certificateLog/list', $data); a part of the page
 *   return $this->redirect('index');
 *
 * LANG is the group of the page's texts ("admin.certificate_log"), BODY_CLASS the CSS class of its content.
 */
abstract class Page
{
    const LANG       = '';
    const BODY_CLASS = '';

    protected $request;
    protected $lang;

    public function __construct(Request $request, Lang $lang)
    {
        $this->request = $request;
        $this->lang    = $lang;
    }

    public function lang(): Lang
    {
        return $this->lang;
    }

    /** @return string page name as used in mg-page ("certificateLog") */
    public function name(): string
    {
        return lcfirst(substr(strrchr(static::class, '\\'), 1));
    }

    /** Title in the page header and the breadcrumb */
    public function title(): string
    {
        return $this->lang->get('admin.menu.' . $this->name());
    }

    /** Text of the page's own group; a key that exists as written is used as it is */
    public function t(string $key, array $replace = []): string
    {
        $own = static::LANG . '.' . $key;

        return $this->lang->get(static::LANG !== '' && $this->lang->has($own) ? $own : $key, $replace);
    }

    public function url(string $action = 'index', array $query = []): string
    {
        return self::pageUrl($this->name(), $action, $query);
    }

    public static function pageUrl(string $page, string $action = 'index', array $query = []): string
    {
        $query = array_filter($query, function($value) {
            return $value !== null && $value !== '' && $value !== 0;
        });

        return 'addonmodules.php?' . http_build_query(['module' => Kernel::module(), 'mg-page' => $page] + ($action !== 'index' ? ['mg-action' => $action] : []) + $query);
    }

    protected function view(string $template, array $data = []): Response
    {
        return Response::html(View::render($template, $data, $this));
    }

    protected function fragment(string $template, array $data = []): Response
    {
        return Response::fragment(View::render($template, $data, $this));
    }

    protected function redirect(string $action = 'index', array $query = []): Response
    {
        return Response::redirect($this->url($action, $query));
    }

    /** A message shown once on the next page view */
    protected function flash(string $text, string $tone = 'ok'): void
    {
        $_SESSION['tssggs_flash'][$this->name()] = ['text' => $text, 'tone' => $tone];
    }

    /** @return array|null ['text' => ..., 'tone' => ...] */
    protected function takeFlash(): ?array
    {
        $flash = $_SESSION['tssggs_flash'][$this->name()] ?? null;
        unset($_SESSION['tssggs_flash'][$this->name()]);

        return is_array($flash) ? $flash : null;
    }
}
