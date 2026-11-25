<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel\Console;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Base of a cron job of the module (app/Cron/<Name>.php, started as "php cron/cron.php <Name>"):
 *
 *   class SSLCertificates extends Command
 *   {
 *       protected $name = 'SSLCertificates';
 *       protected $description = 'SSL Certificates sync.';
 *
 *       protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io) { ... }
 *   }
 *
 * A job runs once at a time (a database lock named after it; a second start is skipped) and from the command line
 * only. "--force" runs it now, whatever its schedule says. An exception ends the job with exit code 1.
 */
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    public const SUCCESS = 0;

    protected $description = '';
    protected $help = '';
    protected $name = null;

    protected function configure()
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description)
            ->setHelp($this->help)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Run now, whatever the schedule of the job says');
        $this->setup();
    }

    /** More arguments and options of the job */
    protected function setup()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if(!in_array(PHP_SAPI, ['cli', 'cli-server'], true) && (isset($_SERVER['SERVER_NAME']) || isset($_SERVER['HTTP_HOST'])))
        {
            $io->error('The cron job must be started from the command line.');
            if(function_exists('logActivity'))
            {
                \logActivity('SSL module: the cron job ' . $this->name . ' was not started from the command line: ' . implode(' ', (array)($_SERVER['argv'] ?? [])));
            }

            return 1;
        }

        try
        {
            if($input->getOption('force'))
            {
                AddonModuleRepository::$forceRun = true;
            }
            $lock = $this->name ? 'tssggs_cron_' . $this->name : null;
            if($lock && !$this->acquireLock($lock))
            {
                $io->warning('The job ' . $this->name . ' is already running, skipped.');

                return 0;
            }
            try
            {
                $this->beforeProcess($input, $output);
                $this->process($input, $output, $io);
                $this->afterProcess($input, $output);
            }
            finally
            {
                if($lock)
                {
                    $this->releaseLock($lock);
                }
            }
        }
        catch(\Exception $e)
        {
            $io->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function acquireLock(string $name): bool
    {
        try
        {
            $row = Capsule::select('SELECT GET_LOCK(?, 0) AS l', [$name]);

            return !empty($row) && (int)$row[0]->l === 1;
        }
        catch(\Throwable $e)
        {
            // no lock available (database without GET_LOCK): the job runs
            return true;
        }
    }

    protected function releaseLock(string $name): void
    {
        try
        {
            Capsule::select('SELECT RELEASE_LOCK(?)', [$name]);
        }
        catch(\Throwable $e)
        {
            // the lock ends with the connection anyway
        }
    }

    protected function beforeProcess(InputInterface $input, OutputInterface $output)
    {
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
    }

    protected function afterProcess(InputInterface $input, OutputInterface $output)
    {
    }
}
