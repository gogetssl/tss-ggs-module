<?php

namespace ModulesGarden\TTSGGSModule\Core\CommandLine;

use Exception;
use ModulesGarden\TTSGGSModule\Core\CommandLine\Exceptions\CommandMustCalledViaCli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function ModulesGarden\TTSGGSModule\Core\translator;

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

            $this->beforeProcess($input, $output);

            $this->process($input, $output, new SymfonyStyle($input, $output));

            $this->afterProcess($input, $output);
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
