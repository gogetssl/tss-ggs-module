<?php

namespace TSSGGSModule\App\Cron;

use TSSGGSModule\App\Kernel\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Sample
 * @package TSSGGSModule\App\Cron
 */
class ReSyncProducts extends Command
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'ReSyncProducts';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Merged into ProductPricingUpdate (kept for existing cron lines)';

    /**
     * Command help text
     * @var string
     */
    protected $help = '';

    /**
     * Configure command
     */
    protected function setup()
    {
    }

    /**
     * Run your custom code
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        // merged into ProductPricingUpdate ("Prices and catalogue update"): kept so that cron lines that still call this
        // command do not fail
        $io->note('ReSyncProducts is part of ProductPricingUpdate now; nothing to do here.');
    }
}
