<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * What an action returns: a full page (inside the module layout), a fragment (a part of a page, for htmx) or a
 * redirect.
 */
final class Response
{
    const PAGE     = 'page';
    const FRAGMENT = 'fragment';
    const REDIRECT = 'redirect';
    const DOWNLOAD = 'download';

    protected $type;
    protected $body;
    protected $status;
    protected $fileName = '';
    protected $mime = '';

    protected function __construct(string $type, string $body, int $status = 200)
    {
        $this->type   = $type;
        $this->body   = $body;
        $this->status = $status;
    }

    /** @param string $html the page's content, already rendered */
    public static function html(string $html, int $status = 200): self
    {
        return new self(self::PAGE, $html, $status);
    }

    public static function fragment(string $html, int $status = 200): self
    {
        return new self(self::FRAGMENT, $html, $status);
    }

    public static function redirect(string $url): self
    {
        return new self(self::REDIRECT, $url, 302);
    }

    /** A file the browser saves (CSV export) */
    public static function download(string $content, string $fileName, string $mime = 'text/csv; charset=utf-8'): self
    {
        $response           = new self(self::DOWNLOAD, $content);
        $response->fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
        $response->mime     = $mime;

        return $response;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function send(Page $page, Request $request): void
    {
        if($this->type === self::PAGE && !$request->isHtmx())
        {
            // WHMCS prints its admin page around this
            echo Layout::render($page, $this->body);

            return;
        }

        // a fragment or a redirect is the whole answer: drop what WHMCS has buffered and stop
        while(ob_get_level() > 0)
        {
            ob_end_clean();
        }
        if($this->type === self::REDIRECT)
        {
            // htmx follows a redirect inside the request; HX-Redirect makes the browser load the page instead
            header($request->isHtmx() ? 'HX-Redirect: ' . $this->body : 'Location: ' . $this->body, true, $request->isHtmx() ? 200 : 302);
            exit;
        }
        http_response_code($this->status);
        if($this->type === self::DOWNLOAD)
        {
            header('Content-Type: ' . $this->mime);
            header('Content-Disposition: attachment; filename="' . $this->fileName . '"');
            echo $this->body;
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $this->body;
        exit;
    }
}
