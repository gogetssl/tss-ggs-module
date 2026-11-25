<?php

namespace ModulesGarden\__MODULE__\App\Cron;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Smoke test (tools/qa.sh smoke): every admin UI class under app/UI/Admin (Widgets, Forms, *Container) that can be
 * built without arguments is built and its loadHtml() called inside WHMCS. Classes that need a selected record
 * (KNOWN_CONTEXT) are skipped. Exit code 1 when any class fails.
 */
class SmokeAdminPages extends \ModulesGarden\__MODULE__\Core\CommandLine\Command
{
    protected $name = 'SmokeAdminPages';
    protected $description = 'Smoke test of the admin pages';
    protected $help = '';

    /** forms opened from a table row: they need the row's record */
    const KNOWN_CONTEXT = ['UI/Admin/Products/Index/DataTables/Products/Forms/ConfigurationForm'];

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $root  = dirname(__DIR__, 2);
        $files = [];
        $it    = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/app/UI/Admin', \FilesystemIterator::SKIP_DOTS));
        foreach($it as $file)
        {
            if($file->getExtension() === 'php' && preg_match('~/(Widgets|Forms)/|Container\.php$~', str_replace('\\', '/', $file->getPathname())))
            {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        $ok = $fail = $skip = 0;
        foreach($files as $path)
        {
            $rel   = substr(str_replace('\\', '/', $path), strlen($root . '/app/'), -4);
            $class = 'ModulesGarden\\__MODULE__\\App\\' . str_replace('/', '\\', $rel);
            if(in_array($rel, self::KNOWN_CONTEXT, true) || !class_exists($class))
            {
                $skip++;
                continue;
            }
            $ref  = new \ReflectionClass($class);
            $ctor = $ref->getConstructor();
            if($ref->isAbstract() || !$ref->hasMethod('loadHtml') || ($ctor && $ctor->getNumberOfRequiredParameters() > 0))
            {
                $skip++;
                continue;
            }
            $level = ob_get_level();
            try
            {
                ob_start();
                $ref->newInstance()->loadHtml();
                $ok++;
            }
            catch(\Throwable $e)
            {
                $fail++;
                $io->writeln('FAIL ' . $rel . ': ' . get_class($e) . ': ' . substr($e->getMessage(), 0, 200) . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            }
            finally
            {
                while(ob_get_level() > $level)
                {
                    ob_end_clean();
                }
            }
        }
        $io->writeln("admin pages built: $ok, failed: $fail, skipped: $skip");

        return $fail ? 1 : 0;
    }
}
