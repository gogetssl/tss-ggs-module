<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\App\Migration\Engine;
use ModulesGarden\TSSGGSModule\App\Migration\Store;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Legacy module migration from the command line (large installs, unattended runs, support).
 *
 *   php -q cron.php MigrateLegacy detect
 *   php -q cron.php MigrateLegacy dry-run  --source=ggs|tss [--no-store]
 *   php -q cron.php MigrateLegacy start    --source=ggs|tss [--no-keys] [--keep-legacy-active] [--no-templates]
 *   php -q cron.php MigrateLegacy run      [--id=N]      (runs batches until finished)
 *   php -q cron.php MigrateLegacy status   [--id=N]
 *   php -q cron.php MigrateLegacy rollback --id=N
 *   php -q cron.php MigrateLegacy housekeeping          (expired backups, old log rows; also run by cron5)
 */
class MigrateLegacy extends AbstractCommand
{
    protected $name        = 'MigrateLegacy';
    protected $description = 'Migrate data of the legacy GoGetSSL / TheSSLStore modules into this module';
    protected $help        = '';

    protected function setup()
    {
        $this->addArgument('task', InputArgument::OPTIONAL, 'detect | dry-run | start | run | status | rollback | housekeeping', 'detect');
        $this->addOption('source', null, InputOption::VALUE_REQUIRED, 'ggs or tss');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'migration run id');
        $this->addOption('no-keys', null, InputOption::VALUE_NONE, 'do not fetch AutoInstall private keys from cPanel');
        $this->addOption('keep-legacy-active', null, InputOption::VALUE_NONE, 'do not deactivate the legacy addon after the switch');
        $this->addOption('no-templates', null, InputOption::VALUE_NONE, 'do not copy the legacy e-mail texts');
        $this->addOption('no-store', null, InputOption::VALUE_NONE, 'dry-run: print the report without storing it (pure read)');
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $task = (string)$input->getArgument('task');
        $io->title('Legacy migration: ' . $task);

        switch($task)
        {
            case 'detect':
                foreach(Detector::detect() as $found)
                {
                    $io->section($found['label'] . ' ' . ($found['version'] ?: '(version unknown)') . ' - ' . $found['support']['band'] . ($found['support']['reason'] ? ': ' . $found['support']['reason'] : ''));
                    $io->writeln(json_encode($found['counts'], JSON_PRETTY_PRINT));
                }
                break;

            case 'dry-run':
                $engine = new Engine($this->source($input));
                $report = $input->getOption('no-store') ? $engine->analyse() : $engine->dryRun('cli');
                $io->writeln(json_encode($report['summary'], JSON_PRETTY_PRINT));
                foreach($report['services'] as $group => $rows)
                {
                    if($rows)
                    {
                        $io->section($group . ' (' . count($rows) . ')');
                        $io->table(['service', 'kind', 'legacy status', 'target', 'remote id', 'reason / warnings'],
                            array_map(function($r) { return [$r['service_id'], $r['kind'], $r['legacy_status'], $r['target_status'], $r['remote_id'], $r['reason'] ?: implode(' | ', $r['warnings'])]; }, array_slice($rows, 0, 200)));
                    }
                }
                break;

            case 'start':
                $engine = new Engine($this->source($input));
                $id     = $engine->start('cli', ['fetch_cpanel_keys' => !$input->getOption('no-keys'), 'deactivate_legacy' => !$input->getOption('keep-legacy-active'), 'copy_templates' => !$input->getOption('no-templates')]);
                $io->success('Migration #' . $id . ' prepared. Running batches...');
                $this->runAll($engine, $id, $io);
                break;

            case 'run':
                $run = $input->getOption('id') ? Store::getRun((int)$input->getOption('id')) : Store::activeRun();
                if(!$run)
                {
                    $io->warning('No migration to run');
                    break;
                }
                $this->runAll(new Engine($run['source']), (int)$run['id'], $io);
                break;

            case 'status':
                $run = $input->getOption('id') ? Store::getRun((int)$input->getOption('id')) : Store::latestRun();
                if(!$run)
                {
                    $io->warning('No migration found');
                    break;
                }
                $io->writeln(json_encode((new Engine($run['source']))->status((int)$run['id']), JSON_PRETTY_PRINT));
                break;

            case 'rollback':
                $id = (int)$input->getOption('id');
                if(!$id)
                {
                    throw new \InvalidArgumentException('--id is required');
                }
                $io->writeln(json_encode(Engine::rollback($id, 'cli'), JSON_PRETTY_PRINT));
                break;

            case 'housekeeping':
                $io->writeln(json_encode(Engine::housekeeping()));
                break;

            default:
                throw new \InvalidArgumentException('Unknown task: ' . $task);
        }

        $io->success('Finished!');
    }

    protected function source(InputInterface $input): string
    {
        $source = strtolower((string)$input->getOption('source'));
        if(!in_array($source, [\ModulesGarden\TSSGGSModule\App\Libs\Edition::get()], true))
        {
            throw new \InvalidArgumentException('--source=' . \ModulesGarden\TSSGGSModule\App\Libs\Edition::get() . ' is required');
        }

        return $source;
    }

    protected function runAll(Engine $engine, int $id, SymfonyStyle $io): void
    {
        do
        {
            $status = $engine->runBatch($id);
            if(!empty($status['busy']))
            {
                // another runner (the admin page) is processing this migration
                sleep(5);
            }
            $io->writeln(sprintf('%s  %d%%  done %d  failed %d  skipped %d  pending %d', $status['run']['status'], $status['percent'],
                $status['jobs']['done'], $status['jobs']['failed'], $status['jobs']['skipped'], $status['jobs']['pending']));
        }
        // a run that is neither processing nor finished (e.g. stopped while preparing) is not waited for
        while(!$status['finished'] && in_array($status['run']['status'], [Store::RUN_RUNNING, Store::RUN_SWITCHING], true));

        if($status['run']['status'] === Store::RUN_FAILED)
        {
            $io->error('Migration failed: ' . $status['run']['last_error']);
        }
        elseif(!empty($status['run']['summary']['exceptions']))
        {
            $io->warning(count($status['run']['summary']['exceptions']) . ' exception(s), see the migration log');
        }
    }
}
