<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The one cron line of the module: starts every job; each job decides itself whether it is due (the frequency chosen
 * under Settings > CRON Settings, counted from its last run). Schedule this command every 5 minutes.
 *
 *   php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php RunAll
 */
class RunAll extends AbstractCommand
{
    protected $name = 'RunAll';

    protected $description = 'Starts all jobs of the module; every job runs only when it is due';

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $background = stripos(PHP_OS, 'WIN') !== 0 && function_exists('exec')
            && !in_array('exec', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true);
        $php        = PHP_BINARY !== '' && is_executable(PHP_BINARY) && stripos(basename(PHP_BINARY), 'fpm') === false ? PHP_BINARY : 'php';
        $script     = ModuleConstants::getFullPath('cron', 'cron.php');

        foreach(CronStatus::TASKS as $job)
        {
            if($background)
            {
                // jobs run side by side: a slow one does not hold up the others
                exec('nohup ' . escapeshellarg($php) . ' -q ' . escapeshellarg($script) . ' ' . escapeshellarg($job) . ($input->getOption('force') ? ' --force' : '') . ' > /dev/null 2>&1 &');
                $io->writeln('Started ' . $job);
                continue;
            }

            $io->writeln('Running ' . $job);
            $this->getApplication()->find($job)->run(new ArrayInput(['command' => $job] + ($input->getOption('force') ? ['--force' => true] : [])), $output);
        }
    }
}
