<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Retired: the pending orders were read one by one every 5 minutes. The order status sync (SSLCertificates,
 * GET /services/updates every 5 minutes) now reads only the orders the vendor reports as changed. Kept so an old
 * cron line "cron.php SSLCertificatesProcessing" still works: it starts the order status sync, which runs when due.
 */
class SSLCertificatesProcessing extends AbstractCommand
{
    protected $name = 'SSLCertificatesProcessing';

    protected $description = 'Retired: starts the order status sync (SSLCertificates)';

    protected $help = '';

    protected function setup()
    {
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $this->getApplication()->find('SSLCertificates')->run(new ArrayInput(['command' => 'SSLCertificates'] + ($input->getOption('force') ? ['--force' => true] : [])), $output);
    }
}
