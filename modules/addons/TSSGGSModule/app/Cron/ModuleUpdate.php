<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use ModulesGarden\TSSGGSModule\App\Libs\Update\Installer;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Preflight;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Release;
use ModulesGarden\TSSGGSModule\App\Libs\Update\State;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Workdir;
use ModulesGarden\TSSGGSModule\App\Kernel\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The module updater from the command line (for hosting staff, or where the admin page can not run it):
 *   php cron/cron.php ModuleUpdate              look for a new release and show the checks
 *   php cron/cron.php ModuleUpdate --install    download, verify, back up and install it
 *   php cron/cron.php ModuleUpdate --rollback   put the previous version back from its backup
 * An update that stopped half way is repaired with: php modules/.<folder>-update/recover.php
 */
class ModuleUpdate extends Command
{
    protected $name = 'ModuleUpdate';

    protected $description = 'Checks for, installs or rolls back a module release (signed releases only)';

    protected function setup()
    {
        $this->addOption('install', null, InputOption::VALUE_NONE, 'install the newest release');
        $this->addOption('rollback', null, InputOption::VALUE_NONE, 'put the previous version back');
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        if($input->getOption('rollback'))
        {
            $result = Installer::rollback();
            $io->success('Rolled back from ' . $result['from'] . ' to ' . $result['to']);

            return;
        }

        $state = Release::check(true);
        $io->writeln('Installed: ' . State::installedVersion() . ', newest release: ' . ($state['version'] ?? '?') . ($state['error'] ? ' (' . $state['error'] . ')' : ''));
        if(!Release::available($state))
        {
            $io->writeln('Nothing to update.');

            return;
        }

        $checks = Preflight::run($state, (bool)$input->getOption('install'));
        foreach($checks as $check)
        {
            $io->writeln(($check['ok'] ? '[ok]   ' : ($check['blocking'] ? '[FAIL] ' : '[warn] ')) . $check['key'] . ($check['detail'] !== '' ? ': ' . $check['detail'] : ''));
        }
        if(!$input->getOption('install'))
        {
            $io->writeln('Run with --install to install ' . $state['version'] . '.');

            return;
        }
        if(!Preflight::passed($checks))
        {
            $io->error('This server can not take the update automatically. Download it from ' . ($state['page'] ?? ''));

            return;
        }

        $io->writeln('Downloading and verifying ' . $state['version'] . ' ...');
        $prepared = Installer::prepare();
        $io->writeln('  ' . $prepared['files'] . ' files verified');
        $io->writeln('Backing up the running version ...');
        $backup = Installer::backup();
        $io->writeln('  ' . Workdir::path('backups', $backup['backup']));
        $io->writeln('Installing ...');
        $result = Installer::install();
        $io->success('Updated from ' . $result['from'] . ' to ' . $result['to']);
    }
}
