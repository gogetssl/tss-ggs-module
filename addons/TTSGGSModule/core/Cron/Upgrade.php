<?php

namespace ModulesGarden\TTSGGSModule\Core\Cron;

use ModulesGarden\TTSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\PatchManager;
use ModulesGarden\TTSGGSModule\Core\Module\Addon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\TableCell;

class Upgrade extends AbstractCommand
{
    public const ACTION_LIST    = 'list';
    public const ACTION_RUN     = 'run';


    protected $name = 'module:upgrade';
    protected array $availableActions = [];

    public function __construct()
    {
        parent::__construct($this->name);

        $this->setAliases(['upgrade']);
        $this->initAvailableActions();
    }

    protected function setup(): void
    {
        $this->addArgument("action", InputArgument::REQUIRED);
        $this->addArgument("params", InputArgument::OPTIONAL);
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        try
        {
            $action = $input->getArgument('action');

            if (!in_array($action, array_keys($this->availableActions)))
            {
                throw new \Exception("The selected action is not allowed. Select the correct action name.");
            }

            call_user_func_array($this->availableActions[$action], [$input, $output, $io]);
        }
        catch (\Exception $e)
        {
            $io->error($e->getMessage());
        }
    }

    protected function initAvailableActions(): void
    {
        $this->availableActions[self::ACTION_LIST] = function ($input, $output, $io) {
            $this->listAction($input, $output, $io);
        };

        $this->availableActions[self::ACTION_RUN] = function ($input, $output, $io) {
            $this->runAction($input, $output, $io);
        };
    }

    protected function listAction($input, $output, $io): void
    {
        $patchManager = new PatchManager();

        $upgrades = $patchManager->getUpdateFiles();

        if (empty($upgrades))
        {
            $io->info('No upgrades available');
            return;
        }

        $headers = [new TableCell('Available upgrades', ['colspan' => 1])];

        $rows = array_map(function ($value) {
            return [$value];
        }, array_keys($upgrades));

        $io->table($headers, $rows);
    }

    protected function runAction($input, $output, $io): void
    {
        $version = $input->getArgument('params');

        if (empty($version))
        {
            throw new \Exception("Version not specified");
        }

        $io->info(sprintf('Run upgrade for version: %s', $version));

        Addon::upgrade(['version' => $version], true);

        $io->success('Upgrade completed successfully');
    }

}