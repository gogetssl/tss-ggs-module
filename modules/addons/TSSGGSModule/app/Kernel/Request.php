<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * The current request: page, action, input, and whether htmx sent it.
 */
final class Request
{
    protected $query;
    protected $post;
    protected $server;

    public function __construct(array $query, array $post, array $server)
    {
        $this->query  = $query;
        $this->post   = $post;
        $this->server = $server;
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function page(): string
    {
        return (string)($this->query['mg-page'] ?? 'home');
    }

    public function action(): string
    {
        return (string)($this->input('mg-action') ?: 'index');
    }

    public function isPost(): bool
    {
        return strtoupper((string)($this->server['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    /** htmx asks for a part of the page: the answer is a fragment, not the whole page */
    public function isHtmx(): bool
    {
        return ($this->server['HTTP_HX_REQUEST'] ?? '') === 'true';
    }

    /** @return mixed posted value first, then the query string */
    public function input(string $name, $default = null)
    {
        return $this->post[$name] ?? $this->query[$name] ?? $default;
    }

    /**
     * @return array everything a form posted, as typed: WHMCS hands request values HTML-encoded (' arrives as &#039;),
     *               which would be saved into the settings; the token and the routing values are left out
     */
    public function posted(): array
    {
        $decode = function($value) use (&$decode) {
            return is_array($value) ? array_map($decode, $value) : html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };

        return $decode(array_diff_key($this->post, array_flip(['token', 'module', 'mg-page', 'mg-action'])));
    }

    public function string(string $name, string $default = ''): string
    {
        $value = $this->input($name, $default);

        return is_scalar($value) ? trim((string)$value) : $default;
    }

    public function int(string $name, int $default = 0): int
    {
        $value = $this->input($name);

        return is_scalar($value) && preg_match('/^-?\d+$/', (string)$value) ? (int)$value : $default;
    }

    /** @return string the value when it is one of $allowed, else $default */
    public function oneOf(string $name, array $allowed, string $default = ''): string
    {
        $value = $this->string($name);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    /** The WHMCS token, from the form field "token" or the header htmx sends (X-CSRF-Token, set by the layout) */
    public function hasValidToken(): bool
    {
        $token = (string)($this->post['token'] ?? $this->server['HTTP_X_CSRF_TOKEN'] ?? '');

        return \ModulesGarden\TSSGGSModule\App\Libs\Support\CsrfToken::valid($token);
    }
}
