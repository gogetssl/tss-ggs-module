<?php

namespace TSSGGSModule\App\Kernel;

/**
 * Registers the addon's WHMCS hooks. Every file in app/Hooks is one hook point, named after the file
 * (app/Hooks/InvoicePaid.php is the hook "InvoicePaid"), and registers its callbacks through $hookManager:
 *
 *   $hookManager->register(function($args) { ... }, 100);     // 100: the priority (lower runs earlier)
 *
 * The hooks of the client area and of the service pages are in the server module (modules/servers/.../hooks.php).
 */
class Hooks
{
    /** @var string the hook point of the file being loaded */
    protected $current = '';

    public static function load(string $dir): void
    {
        $hookManager = new self();
        foreach(glob($dir . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file)
        {
            $hookManager->current = basename($file, '.php');
            require $file;
        }
    }

    public function register(callable $callback, int $priority = 1): void
    {
        if(function_exists('add_hook'))
        {
            \add_hook($this->current, $priority, $callback);
        }
    }
}
