<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use ModulesGarden\TSSGGSModule\App\Migration\TestEnvironment as Env;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Rehearse installation + migration on a development install. Development hosts only (see Env::guard()).
 *
 *   php -q cron.php TestEnvironment snapshot [--refresh]   baseline copy of legacy, unified and touched WHMCS tables
 *   php -q cron.php TestEnvironment reset [--fresh]        back to the baseline; --fresh also clears the wizard state
 *   php -q cron.php TestEnvironment status                 baseline tables with row counts and sizes
 *   php -q cron.php TestEnvironment drop                   remove the baseline tables
 */
class TestEnvironment extends AbstractCommand
{
    protected $name        = 'TestEnvironment';
    protected $description = 'Development only: snapshot and reset the install for repeated migration tests';
    protected $help        = '';

    protected function setup()
    {
        $this->addArgument('task', InputArgument::OPTIONAL, 'snapshot | reset | status | drop', 'status');
        $this->addOption('refresh', null, InputOption::VALUE_NONE, 'snapshot: replace an existing baseline');
        $this->addOption('fresh', null, InputOption::VALUE_NONE, 'reset: also remove the unified module configuration (wizard from step 1)');
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $task = (string)$input->getArgument('task');
        $io->title('Test environment: ' . $task);

        switch($task)
        {
            case 'snapshot':
                $done = Env::snapshot((bool)$input->getOption('refresh'));
                $io->table(['table', 'rows'], array_map(null, array_keys($done), array_values($done)));
                $io->success(count($done) . ' tables copied');
                break;

            case 'reset':
                $r = Env::reset((bool)$input->getOption('fresh'));
                $io->table(['table', 'rows'], array_map(null, array_keys($r['restored']), array_values($r['restored'])));
                if($r['dropped'])
                {
                    $io->writeln('Dropped: ' . implode(', ', $r['dropped']));
                }
                $io->success(count($r['restored']) . ' tables restored' . ($r['fresh'] ? ', module configuration cleared' : ''));
                break;

            case 'drop':
                $io->success(Env::dropBaseline() . ' baseline tables dropped');
                break;

            default:
                $rows = Env::baselineTables();
                if(!$rows)
                {
                    $io->warning('No baseline. Run: php -q cron.php TestEnvironment snapshot');
                    break;
                }
                $io->table(['table', 'rows', 'kB'], array_map(function($t, $i) { return [$t, $i['rows'], round($i['bytes'] / 1024)]; }, array_keys($rows), $rows));
                break;
        }

        return 0;
    }
}
