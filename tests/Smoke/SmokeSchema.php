<?php

namespace ModulesGarden\__MODULE__\App\Cron;

use ModulesGarden\__MODULE__\App\Database\Schema;
use ModulesGarden\__MODULE__\App\Libs\Support\Edition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Smoke test (tools/qa.sh smoke): the database is at the latest App\Database\Schema version and every table and
 * column the steps add is there. Read-only. Exit code 1 when anything is missing.
 */
class SmokeSchema extends \ModulesGarden\__MODULE__\Core\CommandLine\Command
{
    protected $name = 'SmokeSchema';
    protected $description = 'Smoke test of the database schema';
    protected $help = '';

    const EXPECTED = [
        'Requests'          => ['serviceid'],
        'AcmeSubscriptions' => ['account_status'],
        'AisSubscriptions'  => ['account_status', 'install_method', 'acme_account_id', 'eab_kid', 'eab_hmac_key', 'server_url'],
        'ClientMarkups'     => ['client_id', 'product_id', 'markup'],
        'CatalogueChanges'  => ['vendor', 'remote_id', 'emailed_at'],
        'AutoInstallReport' => ['service_id', 'status', 'late_at'],
        'AutoInstallQueue'  => ['service_id', 'status', 'attempts'],
        'AutoInstallLogs'   => ['service_id', 'message'],
    ];

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $missing = [];
        $latest  = max(array_keys(Schema::STEPS));
        $version = Schema::version();
        if($version !== $latest)
        {
            $missing[] = 'schema version ' . $version . ', expected ' . $latest;
        }
        $schema = Capsule::schema();
        foreach(self::EXPECTED as $name => $columns)
        {
            $table = Edition::table($name);
            if(!$schema->hasTable($table))
            {
                $missing[] = 'table ' . $table;
                continue;
            }
            foreach($columns as $column)
            {
                if(!$schema->hasColumn($table, $column))
                {
                    $missing[] = $table . '.' . $column;
                }
            }
        }
        if($schema->hasTable(Edition::table('DemonTask')))
        {
            $missing[] = 'DemonTask still there';
        }
        $io->writeln('schema version ' . $version . ($missing ? '; MISSING: ' . implode(', ', $missing) : '; all tables and columns there'));

        return $missing ? 1 : 0;
    }
}
