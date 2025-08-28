<?php

namespace ModulesGarden\TTSGGSModule\Core\CommandLine;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 */
class Command extends AbstractCommand
{
    final protected function configure()
    {
        parent::configure();
    }

    /**
     * Execute command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return parent::execute($input, $output);
    }
}
