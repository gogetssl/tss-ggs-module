<?php

namespace ModulesGarden\__MODULE__\App\Cron;

use ModulesGarden\__MODULE__\App\Kernel\Lang;
use ModulesGarden\__MODULE__\App\Kernel\Layout;
use ModulesGarden\__MODULE__\App\Kernel\Request;
use ModulesGarden\__MODULE__\App\Kernel\Response;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Smoke test (tools/qa.sh smoke): every page on the module's own kernel (app/Pages/Admin) is built inside WHMCS -
 * the full page in the layout and the htmx answer of its index action. Read-only. Exit code 1 when a page fails
 * or its HTML is not what the layout / a fragment must be.
 */
class SmokeKernelPages extends \ModulesGarden\__MODULE__\App\Kernel\Console\Command
{
    protected $name = 'SmokeKernelPages';
    protected $description = 'Smoke test of the pages on the own kernel';
    protected $help = '';

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $ok = $fail = 0;
        foreach(glob(dirname(__DIR__) . '/Pages/Admin/*.php') as $file)
        {
            $name  = basename($file, '.php');
            $class = 'ModulesGarden\\__MODULE__\\App\\Pages\\Admin\\' . $name;
            try
            {
                foreach([false, true] as $htmx)
                {
                    $request  = new Request(['mg-page' => lcfirst($name)], [], ['REQUEST_METHOD' => 'GET'] + ($htmx ? ['HTTP_HX_REQUEST' => 'true'] : []));
                    $page     = new $class($request, new Lang('admin', 'english'));
                    $response = $page->index();
                    if(!$response instanceof Response || trim($response->body()) === '')
                    {
                        throw new \RuntimeException('index() gave no content' . ($htmx ? ' (htmx)' : ''));
                    }
                    if(!$htmx)
                    {
                        $html = Layout::render($page, $response->body());
                        // a page with its own heading (the dashboard) gets none from the layout; the setup wizard has no menu
                        foreach(array_merge(['id="layers2"', 'vendor/htmx.js', 'vendor/alpine.js'], $page::CHROME ? ['lu-nav__item'] : [], $page::HEADING ? ['pagehead__title'] : []) as $must)
                        {
                            if(strpos($html, $must) === false)
                            {
                                throw new \RuntimeException('layout without ' . $must);
                            }
                        }
                    }
                    elseif($response->type() === Response::PAGE && strpos($response->body(), 'id="layers2"') !== false)
                    {
                        throw new \RuntimeException('htmx answer contains the layout');
                    }
                }
                // the parts a page loads after it is shown (the dashboard panels, the reports, the charts)
                foreach(['PANELS' => 'panel', 'REPORTS' => 'report', 'CHARTS' => 'chart'] as $const => $action)
                {
                    if(!defined($class . '::' . $const))
                    {
                        continue;
                    }
                    $parts = constant($class . '::' . $const);
                    foreach(array_keys($parts) === range(0, count($parts) - 1) ? $parts : array_keys($parts) as $part)
                    {
                        $request  = new Request(['mg-page' => lcfirst($name), 'mg-action' => $action, 'name' => $part], [], ['REQUEST_METHOD' => 'GET', 'HTTP_HX_REQUEST' => 'true']);
                        $response = (new $class($request, new Lang('admin', 'english')))->$action();
                        if(!$response instanceof Response || trim($response->body()) === '')
                        {
                            throw new \RuntimeException($action . ' "' . $part . '" gave no content');
                        }
                        if(strpos($response->body(), '-note is-warn') !== false && strpos($response->body(), 'does not exist') !== false)
                        {
                            throw new \RuntimeException($action . ' "' . $part . '" is not known to the page');
                        }
                    }
                }
                // every page of the setup wizard (a finalized wizard answers with a redirect to the dashboard)
                foreach(defined($class . '::WIZARD') ? \ModulesGarden\__MODULE__\App\Libs\Setup\Wizard::PAGES : [] as $step)
                {
                    $request  = new Request(['mg-page' => lcfirst($name), 'mg-action' => 'step' . $step], [], ['REQUEST_METHOD' => 'GET']);
                    $response = (new $class($request, new Lang('admin', 'english')))->{'step' . $step}();
                    if(!$response instanceof Response || trim($response->body()) === '')
                    {
                        throw new \RuntimeException('step ' . $step . ' gave no content');
                    }
                }
                // every screen of a page with screens (Settings)
                foreach(defined($class . '::SCREENS') ? array_keys(constant($class . '::SCREENS')) : [] as $screen)
                {
                    $request  = new Request(['mg-page' => lcfirst($name), 'settings-page' => $screen], [], ['REQUEST_METHOD' => 'GET']);
                    $response = (new $class($request, new Lang('admin', 'english')))->index();
                    if(!$response instanceof Response || strpos($response->body(), 'lu-widget__body') === false)
                    {
                        throw new \RuntimeException('screen "' . $screen . '" gave no content');
                    }
                }
                $ok++;
            }
            catch(\Throwable $e)
            {
                $fail++;
                $io->writeln('FAIL ' . $name . ': ' . get_class($e) . ': ' . substr($e->getMessage(), 0, 300) . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            }
        }
        $io->writeln("kernel pages built: $ok, failed: $fail");

        return $fail ? 1 : 0;
    }
}
