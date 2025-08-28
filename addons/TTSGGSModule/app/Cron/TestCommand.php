<?php

namespace ModulesGarden\TTSGGSModule\App\Cron;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends \ModulesGarden\TTSGGSModule\Core\CommandLine\Command
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'testCommand';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Test Command description';

    /**
     * Command help text
     * @var string
     */
    protected $help = 'Please do not run this command!';

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        echo "Some Parameter: " . $input->getParameterOption('someParam', false, true);

        throw new \Exception("Some error");
    }

}