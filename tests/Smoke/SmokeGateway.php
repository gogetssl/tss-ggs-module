<?php

namespace ModulesGarden\__MODULE__\App\Cron;

use ModulesGarden\__MODULE__\App\Libs\Edition;
use ModulesGarden\__MODULE__\App\Libs\Gateway\GatewayException;
use ModulesGarden\__MODULE__\App\Libs\SSLTrustCenterApi;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Smoke test (tools/qa.sh smoke): read-only calls to the gateway with the stored credentials of every connected
 * vendor - credentials check, product list, and an order that does not exist (must come back as a GatewayException
 * with its HTTP status and the gateway's error code). Nothing is ordered or changed. Exit code 1 on any failure.
 */
class SmokeGateway extends \ModulesGarden\__MODULE__\Core\CommandLine\Command
{
    protected $name = 'SmokeGateway';
    protected $description = 'Smoke test of the gateway client (read-only)';
    protected $help = '';

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $failed = 0;
        foreach(Edition::vendors() as $vendor)
        {
            try
            {
                $api = SSLTrustCenterApi::forVendor($vendor);
            }
            catch(\Throwable $e)
            {
                $io->writeln($vendor . ': not connected (' . $e->getMessage() . ') - skipped');
                continue;
            }
            $mode = $api->isSandbox() ? 'sandbox' : 'live';
            try
            {
                $api->validate();
                $products = $api->getProducts();
                $io->writeln($vendor . ' (' . $mode . '): credentials OK, ' . count((array)$products) . ' products');
            }
            catch(\Throwable $e)
            {
                $failed++;
                $io->writeln('FAIL ' . $vendor . ' (' . $mode . '): ' . get_class($e) . ': ' . $e->getMessage());
                continue;
            }
            try
            {
                $api->getOrder(999999999);
                $failed++;
                $io->writeln('FAIL ' . $vendor . ': order 999999999 was found');
            }
            catch(GatewayException $e)
            {
                $io->writeln($vendor . ': unknown order -> GatewayException HTTP ' . $e->getHttpCode() . ' code "' . $e->getGatewayCode() . '": ' . $e->getMessage());
            }
            catch(\Throwable $e)
            {
                $failed++;
                $io->writeln('FAIL ' . $vendor . ': unknown order threw ' . get_class($e) . ' instead of GatewayException: ' . $e->getMessage());
            }
        }

        return $failed ? 1 : 0;
    }
}
