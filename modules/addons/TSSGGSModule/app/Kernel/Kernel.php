<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * The module's own small kernel (replaces the ModulesGarden framework page by page).
 *
 * A page is one class in app/Pages/<Area>/<Page>.php (Area: Admin). The request names it with mg-page and the
 * action with mg-action (default "index"):
 *
 *   GET  ...&mg-page=certificateLog                 -> Pages\Admin\CertificateLog::index()
 *   GET  ...&mg-page=certificateLog&mg-action=x     -> ::x()
 *   POST ...&mg-page=certificateLog&mg-action=clear -> ::postClear()   (the WHMCS token is checked first)
 *
 * A page that has no class here is still served by the old framework (see Configuration/Addon/base.php), so pages
 * move over one at a time. Templates: resources/templates, a copy in overrides/templates wins (View).
 */
final class Kernel
{
    /** @return string the module's folder name (TSSPlugin / GGSModule in a build) */
    public static function module(): string
    {
        return basename(self::path());
    }

    /** @return string absolute path of the addon module, or of a file / folder inside it */
    public static function path(string ...$parts): string
    {
        return dirname(__DIR__, 2) . ($parts ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) : '');
    }

    public static function pageClass(string $area, string $page): ?string
    {
        if(!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $page))
        {
            return null;
        }
        $class = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\')) . '\\Pages\\' . ucfirst($area) . '\\' . ucfirst($page);

        return is_file(self::path('app', 'Pages', ucfirst($area), ucfirst($page) . '.php')) && class_exists($class) ? $class : null;
    }

    public static function handles(string $area, $page): bool
    {
        return is_string($page) && self::pageClass($area, $page) !== null;
    }

    /**
     * Runs the requested page and prints the result. A full page is printed into the WHMCS admin page; a fragment
     * (htmx request) or a redirect ends the request, so WHMCS adds nothing around it.
     */
    public static function run(string $area, Request $request = null): void
    {
        $request = $request ?: Request::fromGlobals();
        $class   = self::pageClass($area, $request->page());
        /** @var Page $page */
        $page    = new $class($request, new Lang($area));

        try
        {
            $response = self::dispatch($page, $request);
        }
        catch(\Throwable $e)
        {
            $response = Response::html(Layout::error($page, $e), 500);
            if(function_exists('logActivity'))
            {
                \logActivity(self::module() . ': page ' . $request->page() . ' failed: ' . $e->getMessage());
            }
        }

        $response->send($page, $request);
    }

    protected static function dispatch(Page $page, Request $request): Response
    {
        $action = $request->action();
        if(!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $action))
        {
            return Response::html(Layout::notFound($page), 404);
        }

        if($request->isPost())
        {
            $method = 'post' . ucfirst($action);
            if(!$request->hasValidToken())
            {
                return Response::html(Layout::message($page, $page->lang()->get('admin.kernel.tokenExpired'), 'bad'), 403);
            }
        }
        else
        {
            $method = $action;
        }

        // only the page's own public actions: nothing inherited from Page, nothing starting with "post" on a GET
        $ref = new \ReflectionClass($page);
        if(!$ref->hasMethod($method) || !$ref->getMethod($method)->isPublic() || $ref->getMethod($method)->getDeclaringClass()->getName() === Page::class
            || (!$request->isPost() && stripos($method, 'post') === 0))
        {
            return Response::html(Layout::notFound($page), 404);
        }

        $result = $page->$method();

        return $result instanceof Response ? $result : Response::html((string)$result);
    }
}
