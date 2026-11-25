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
class SmokeKernelPages extends \ModulesGarden\__MODULE__\Core\CommandLine\Command
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
                        foreach(['id="layers2"', 'lu-nav__item', 'tssggs-pagehead__title', 'vendor/htmx.js', 'vendor/alpine.js'] as $must)
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
