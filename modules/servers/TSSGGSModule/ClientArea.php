<?php

namespace TSSGGSModule\Server;

use TSSGGSModule\Server\app\Client\Home;

/**
 * The module's part of the client area service page (TSSGGSModule_ClientArea).
 *
 * A page is a method of app\Client\Home named <action>HTML, a background call (the page's own scripts, ?json=1) one
 * named <action>JSON; "mod-action" names it, "index" when empty. Both get the request with the service WHMCS checked
 * the owner of; a page answers ['tpl' => template under templates/client/pages, 'vars' => [...]], a call an array
 * (success / error / data).
 */
class ClientArea
{
    /** text group the page templates start in ($_LANG['serverCA']['home'] of langs/*.php) */
    const TEXT_BASE = ['serverCA', 'home'];

    /** The answer WHMCS renders: templates/client/main.tpl around the page */
    public static function page(array $params, array $input): array
    {
        $vars = [
            'assetsURL'   => self::assetsUrl(),
            'mainJSONURL' => 'clientarea.php?action=productdetails&id=' . (int)$params['serviceid'] . '&json=true',
            'content'     => '',
            'success'     => false,
            'error'       => false,
        ];
        try
        {
            $result = self::run(self::action($input) . 'HTML', $params, $input);
            if(empty($result['tpl']))
            {
                throw new \RuntimeException('The page gave no template');
            }
            $pageVars        = (array)($result['vars'] ?? []);
            $vars['content'] = View::render('pages/' . $result['tpl'], $pageVars, self::TEXT_BASE);
            $vars['success'] = $pageVars['success'] ?? false;
            $vars['error']   = $pageVars['error'] ?? false;
        }
        catch(\Throwable $e)
        {
            $vars['error'] = Text::get('generalError') . self::reference($e);
        }

        return ['templatefile' => 'templates/client/main', 'vars' => $vars];
    }

    /** The answer of a background call: always JSON, never HTML */
    public static function json(array $params, array $input): string
    {
        $content = [];
        try
        {
            $result = (array)self::run(self::action($input) . 'JSON', $params, $input);
            if(!empty($result['error']))
            {
                $content['error']  = $result['error'];
                $content['result'] = 'error';
            }
            elseif(!empty($result['success']))
            {
                $content['success'] = $result['success'];
                $content['result']  = 'success';
            }
            $content['data'] = $result;
        }
        catch(\Throwable $e)
        {
            $content['result'] = 'error';
            $content['error']  = ($e->getMessage() !== '' ? $e->getMessage() : Text::get('generalError')) . self::reference($e);
        }

        return json_encode($content, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /** URL of templates/client/assets, relative to the WHMCS client area */
    public static function assetsUrl(): string
    {
        return 'modules/servers/' . basename(__DIR__) . '/templates/client/assets';
    }

    protected static function action(array $input): string
    {
        $action = (string)($input['mod-action'] ?? '');

        return preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $action) ? $action : 'index';
    }

    /**
     * Only a public instance method of the page class itself is an address: a helper or an inherited method is not
     */
    protected static function run(string $method, array $params, array $input)
    {
        if(!method_exists(Home::class, $method))
        {
            throw new \RuntimeException('Unknown action');
        }
        $reflection = new \ReflectionMethod(Home::class, $method);
        if(!$reflection->isPublic() || $reflection->isStatic())
        {
            throw new \RuntimeException('Unknown action');
        }
        $input['params'] = $params;
        // the service WHMCS checked the owner of, never the request's own "id"
        $input['id']     = (int)$params['serviceid'];

        return (new Home())->{$method}($input);
    }

    /** " Reference: <token>" of an error the module logged, so support can find it */
    protected static function reference(\Throwable $e): string
    {
        return method_exists($e, 'getToken') ? ' ' . Text::get('token') . $e->getToken() : '';
    }
}
