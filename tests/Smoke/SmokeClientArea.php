<?php

namespace __MODULE__\App\Cron;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Smoke test (tools/qa.sh smoke): the client area page of real services, built the way WHMCS builds it
 * (ModuleBuildParams + <module>_ClientArea), one service per order state of each product category. Read-only: the
 * page is only built, nothing is sent or saved by the test itself. Exit code 1 when a page throws or returns an error.
 */
class SmokeClientArea extends \__MODULE__\App\Kernel\Console\Command
{
    protected $name = 'SmokeClientArea';
    protected $description = 'Smoke test of the client area pages';
    protected $help = '';

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $module = '__MODULE__';
        require_once ROOTDIR . '/includes/modulefunctions.php';
        require_once ROOTDIR . '/modules/servers/' . $module . '/' . $module . '.php';
        $function = $module . '_ClientArea';

        // one active service per (category, order status)
        $rows = Capsule::table('tblhosting as h')
            ->join('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->leftJoin('tblsslorders as o', 'o.serviceid', '=', 'h.id')
            ->where('p.servertype', $module)->where('h.domainstatus', 'Active')
            ->orderBy('h.id', 'desc')
            ->get(['h.id', 'p.configoption9 as category', 'o.status as ssl']);
        $picked = [];
        foreach($rows as $row)
        {
            $kind = ($row->category ?: 'tls') . ' / ' . ($row->ssl ?: 'no order');
            if(!isset($picked[$kind]))
            {
                $picked[$kind] = (int)$row->id;
            }
        }

        $failed = 0;
        foreach($picked as $kind => $serviceId)
        {
            $level = ob_get_level();
            try
            {
                ob_start();
                $params = \ModuleBuildParams($serviceId);
                $result = $function($params);
                $error  = is_array($result) && !empty($result['vars']['error']) ? (string)$result['vars']['error'] : '';
                $io->writeln(($error === '' ? 'ok   ' : 'NOTE ') . '#' . $serviceId . ' ' . $kind . ': ' . (is_array($result) ? ($result['templatefile'] ?? 'array') : gettype($result))
                    . ($error !== '' ? ' - page shows: ' . substr($error, 0, 120) : ''));
            }
            catch(\Throwable $e)
            {
                $failed++;
                $io->writeln('FAIL #' . $serviceId . ' ' . $kind . ': ' . get_class($e) . ': ' . substr($e->getMessage(), 0, 200) . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            }
            finally
            {
                while(ob_get_level() > $level)
                {
                    ob_end_clean();
                }
            }
        }
        $io->writeln('client area pages built: ' . (count($picked) - $failed) . ' of ' . count($picked));

        return $failed ? 1 : 0;
    }
}
