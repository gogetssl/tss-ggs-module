<?php

namespace TSSGGSModule\Server;

/**
 * Renders a client area template of the module with the Smarty WHMCS ships. Templates live in templates/client;
 * each gets its variables and $text (Text: {$text->t('key')}), nothing else.
 */
class View
{
    /** templates/client, the folder of every client area template */
    public static function dir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'client';
    }

    /**
     * @param string $template path under templates/client without .tpl, e.g. "pages/home_lei"
     * @param array  $vars     template variables
     * @param array  $textBase the group of the language file the template's $text->t() starts in
     */
    public static function render(string $template, array $vars = [], array $textBase = []): string
    {
        $file = self::dir() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.tpl';
        if(strpos($template, '..') !== false || !is_file($file))
        {
            throw new \RuntimeException('Unknown template ' . $template);
        }

        $smarty = new \Smarty();
        $smarty->setTemplateDir(self::dir());
        $smarty->setCompileDir(self::compileDir());
        $smarty->setErrorReporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        $smarty->assign($vars);
        $smarty->assign('text', new Text($textBase));

        try
        {
            return (string)$smarty->fetch($template . '.tpl');
        }
        catch(\Throwable $e)
        {
            // two requests compiling the same new template at once (right after an update): the second look finds it
            usleep(150000);

            return (string)$smarty->fetch($template . '.tpl');
        }
    }

    protected static function compileDir(): string
    {
        $dir = (string)($GLOBALS['templates_compiledir'] ?? '');
        if($dir !== '' && !preg_match('#^([A-Za-z]:)?[\\\\/]#', $dir) && defined('ROOTDIR'))
        {
            $dir = ROOTDIR . DIRECTORY_SEPARATOR . $dir;
        }

        return $dir !== '' && is_dir($dir) && is_writable($dir) ? $dir : sys_get_temp_dir();
    }
}
