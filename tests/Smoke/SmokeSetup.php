<?php

namespace __MODULE__\App\Cron;

use __MODULE__\App\Kernel\Lang;
use __MODULE__\App\Libs\Settings\ApiSettingsProvider;
use __MODULE__\App\Libs\Support\Edition;
use __MODULE__\App\Libs\Support\Helpers;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Smoke test (tools/qa.sh smoke): money of a client is converted from the client's own currency, and the setup
 * wizard's API page works with the edition's own vendor only. Read-only. Exit code 1 when a check fails.
 */
class SmokeSetup extends \__MODULE__\App\Kernel\Console\Command
{
    protected $name = 'SmokeSetup';
    protected $description = 'Smoke test of the currency conversion and the wizard API page';
    protected $help = '';

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $failed = [];

        // a PHP warning inside the checked code is a failure (the conversion used to read ->id of a number)
        $warnings = [];
        set_error_handler(function($level, $message, $file, $line) use (&$warnings) {
            $warnings[] = $message . ' in ' . basename($file) . ':' . $line;

            return true;
        });

        $default = (int)Capsule::table('tblcurrencies')->where('default', 1)->value('id');
        $checked = 0;
        foreach(Capsule::table('tblclients')->select('currency', Capsule::raw('MIN(id) AS id'))->groupBy('currency')->get() as $row)
        {
            $expected = \convertCurrency(100.0, (int)$row->currency, $default);
            $got      = Helpers::clientCurrencyToDefaultCurrency(100, (int)$row->id);
            $checked++;
            if(abs((float)$expected - (float)$got) > 0.0001)
            {
                $failed[] = 'currency ' . $row->currency . ': 100 -> ' . $got . ', expected ' . $expected;
            }
        }
        $io->writeln('currency conversion: ' . $checked . ' client currencies checked');

        // the wizard's API page: only the edition's own vendor is tested, whatever is connected
        $provider = (new ApiSettingsProvider([], new Lang('admin', 'english')))->ownVendorOnly();
        $method   = new \ReflectionMethod($provider, 'chosenVendors');
        $method->setAccessible(true);
        $vendors = $method->invoke($provider);
        if($vendors !== [Edition::primaryVendor()])
        {
            $failed[] = 'wizard API page tests ' . implode(',', $vendors);
        }
        $io->writeln('wizard API page: tests ' . implode(',', $vendors) . ' (connected besides: ' . (Edition::connectedVendor() ?: 'none') . ')');

        restore_error_handler();
        foreach($warnings as $warning)
        {
            $failed[] = 'PHP warning: ' . $warning;
        }
        if($failed)
        {
            $io->writeln('FAILED: ' . implode('; ', $failed));
        }

        return $failed ? 1 : 0;
    }
}
