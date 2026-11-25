<?php

namespace TSSGGSModule\App\Kernel;

/**
 * The module's own small kernel: it serves the admin pages.
 *
 * A page is one class in app/Pages/<Area>/<Page>.php (Area: Admin). The request names it with mod-page and the
 * action with mod-action (default "index"):
 *
 *   GET  ...&mod-page=certificateLog                 -> Pages\Admin\CertificateLog::index()
 *   GET  ...&mod-page=certificateLog&mod-action=x     -> ::x()
 *   POST ...&mod-page=certificateLog&mod-action=clear -> ::postClear()   (the WHMCS token is checked first)
 *
 * A page that has no class here answers "This page does not exist". Templates: resources/templates, a copy in overrides/templates wins (View).
 */
final class Kernel
{
    /** pages that need the vendor API: until the setup wizard has connected it, they lead to the wizard */
    const SETUP_FIRST = ['home', 'products', 'import', 'statistics', 'reports', 'pricing', 'settings'];

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

    /** @return string URL of the module's resources folder (assets/css, assets/js, assets/img, build) */
    public static function assetsUrl(): string
    {
        return rtrim((string)($GLOBALS['CONFIG']['SystemURL'] ?? ''), '/') . '/modules/addons/' . self::module() . '/resources';
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

    /**
     * @param array|null $query the request's query string; a page that is only partly moved (Settings) says through
     *                          its static serves($query) which of its screens the kernel serves already
     */
    public static function handles(string $area, $page, array $query = null): bool
    {
        $class = is_string($page) ? self::pageClass($area, $page) : null;
        if($class === null)
        {
            return false;
        }

        return !method_exists($class, 'serves') || $class::serves($query ?? $_GET);
    }

    /**
     * Runs the requested page and prints the result. A full page is printed into the WHMCS admin page; a fragment
     * (htmx request) or a redirect ends the request, so WHMCS adds nothing around it.
     */
    public static function run(string $area, Request $request = null): void
    {
        $request = $request ?: Request::fromGlobals();
        $class   = self::pageClass($area, $request->page());
        if($class === null)
        {
            // no such page: said inside the module's layout (the dashboard page lends it)
            $home = self::pageClass($area, 'home');
            $page = new $home($request, new Lang($area));
            Response::html(Layout::notFound($page), 404)->send($page, $request);

            return;
        }
        /** @var Page $page */
        $page    = new $class($request, new Lang($area));

        try
        {
            $response = in_array($page->name(), self::SETUP_FIRST, true) && !self::connected()
                ? Response::redirect(Page::pageUrl('configuration', 'step1'))
                : self::dispatch($page, $request);
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

    /** The setup wizard has tested the API credentials successfully */
    public static function connected(): bool
    {
        $configuration = (new \TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration();

        return !empty($configuration) && ($configuration['test_connection'] ?? 'error') !== 'error';
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
