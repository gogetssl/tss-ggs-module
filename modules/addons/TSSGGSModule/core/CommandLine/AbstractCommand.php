<?php

namespace ModulesGarden\TSSGGSModule\Core\CommandLine;

use Exception;
use ModulesGarden\TSSGGSModule\Core\CommandLine\Exceptions\CommandMustCalledViaCli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function ModulesGarden\TSSGGSModule\Core\translator;

/**
 * Class Command
 */
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Success error code
     * @var int
     */
    public const SUCCESS = 0;

    /**
     * Command description
     * @var string
     */
    protected $description = '';

    /**
     * Command help text. Use --help to show
     * @var string
     */
    protected $help = '';

    /**
     * Command name
     * @var string
     */
    protected $name = null;

    /**
     * minimal command configuration
     */
    protected function configure()
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description)
            ->setHelp($this->help);

        $this->setup();
    }

    /**
     * Add some custom actions here based on documentation  https://symfony.com/doc/current/console.html
     */
    protected function setup()
    {
    }

    /**
     * Execute command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            if (!CommandHelper::calledViaCli())
            {
                throw new CommandMustCalledViaCli();
            }

            // one run of a job at a time: a run that is still going (or a second cron entry) makes the next one skip
            $lock = $this->name ? 'tssggs_cron_' . $this->name : null;
            if($lock && !$this->acquireLock($lock))
            {
                (new SymfonyStyle($input, $output))->warning('The job ' . $this->name . ' is already running, skipped.');

                return 0;
            }

            try
            {
                $this->beforeProcess($input, $output);

                $this->process($input, $output, new SymfonyStyle($input, $output));

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
        catch (CommandMustCalledViaCli $ex)
        {
            (new SymfonyStyle($input, $output))->error(translator()->get('cronJob.error.cronMustByCalledViaCli.console'));

            logActivity(translator()->get('cronJob.error.cronMustByCalledViaCli.log',
                ['cronJobName' => $this->name, 'cronFullName' => implode(" ", $_SERVER['argv'])]));

            return 1;
        }
        catch (Exception $ex)
        {
            (new SymfonyStyle($input, $output))->error($ex->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * MySQL named lock, held by this connection until released or the process ends; false when another run holds it
     */
    protected function acquireLock(string $name): bool
    {
        try
        {
            $row = \WHMCS\Database\Capsule::select('SELECT GET_LOCK(?, 0) AS l', [$name]);

            return !empty($row) && (int)$row[0]->l === 1;
        }
        catch (\Throwable $e)
        {
            // no database lock available: run anyway rather than never
            return true;
        }
    }

    protected function releaseLock(string $name): void
    {
        try
        {
            \WHMCS\Database\Capsule::select('SELECT RELEASE_LOCK(?)', [$name]);
        }
        catch (\Throwable $e)
        {
        }
    }

    /**
     * Function will be called before executing "process" function
     * @throws Exception
     */
    protected function beforeProcess(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
    }

    /**
     * Function will be called after executing "process" function
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function afterProcess(InputInterface $input, OutputInterface $output)
    {
    }
}
