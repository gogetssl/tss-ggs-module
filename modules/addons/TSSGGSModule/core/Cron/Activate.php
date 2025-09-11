<?php

namespace ModulesGarden\TSSGGSModule\Core\Cron;

use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Core\Module\Addon;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Activate extends AbstractCommand
{
    protected $name = 'module:activate';

    public function __construct()
    {
        parent::__construct($this->name);

        $this->setAliases(['activate']);
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        try
        {
            $io->info('Run module activate');

            Addon::activate();

            $io->success('Module activated successfully');
        }
        catch (\Exception $ex)
        {
            $io->error($ex->getMessage());
        }
    }

}