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
    protected $headers = [];

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

    /** Data for a script of the page (the migration runner asks for the progress) */
    public static function json(array $data, int $status = 200): self
    {
        $response       = new self(self::DOWNLOAD, (string)json_encode($data), $status);
        $response->mime = 'application/json; charset=utf-8';

        return $response;
    }

    /**
     * The answer of a dialog's form that worked: the fragment goes where the form says (the list behind the dialog)
     * and the dialog closes (app.js).
     */
    public function closesDialog(): self
    {
        $this->headers['X-Module-Dialog'] = 'close';

        return $this;
    }

    /**
     * The answer of a dialog's form that did not work, or the dialog itself: the fragment (the dialog again, with the
     * error) goes into the dialog's place instead of the form's target.
     */
    public function intoDialog(): self
    {
        $this->headers['HX-Retarget'] = '#tssggs-dialog';
        $this->headers['HX-Reswap']   = 'innerHTML';

        return $this;
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
        // tells app.js that this answer is the kernel's own (an error page of WHMCS or the web server is not put
        // into the page)
        header('X-Module-Kernel: 1');
        foreach($this->headers as $name => $value)
        {
            header($name . ': ' . $value);
        }
        if($this->type === self::DOWNLOAD)
        {
            header('Content-Type: ' . $this->mime);
            if($this->fileName !== '')
            {
                header('Content-Disposition: attachment; filename="' . $this->fileName . '"');
                header('X-Content-Type-Options: nosniff');
            }
            echo $this->body;
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $this->body;
        exit;
    }
}
